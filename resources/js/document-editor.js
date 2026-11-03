import { Canvas, FabricImage, Point, Rect, Textbox } from 'fabric';

const pageWidth = 794;
const pageHeight = 1123;
const componentKeys = ['cover', 'header', 'body', 'footer', 'signature', 'other'];

document.querySelectorAll('[data-document-editor]').forEach((root) => {
    initializeDocumentEditor(root);
});

function initializeDocumentEditor(root) {
    const canvasElement = root.querySelector('[data-fabric-canvas]');
    const canvas = new Canvas(canvasElement, {
        width: pageWidth,
        height: pageHeight,
        backgroundColor: '#ffffff',
        preserveObjectStacking: true,
        selection: true,
    });
    const initialData = parseBase64Json(root.dataset.canvasData);
    const pages = Array.isArray(initialData.pages) && initialData.pages.length
        ? initialData.pages
        : [createPage(1)];
    const history = [];
    let historyIndex = -1;
    let currentPageIndex = 0;
    let restoringCanvas = false;
    let saveTimer;
    let zoomScale = 1;

    const status = root.querySelector('[data-editor-status]');
    const layerList = root.querySelector('[data-layer-list]');
    const layerEmpty = root.querySelector('[data-layers-empty]');
    const pageIndicator = root.querySelector('[data-page-indicator]');
    const assetList = root.querySelector('[data-asset-list]');
    const assetsEmpty = root.querySelector('[data-assets-empty]');
    const layerSelect = root.querySelector('[data-layer-select]');
    const layerObjects = new Map();
    const layerIds = new WeakMap();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let nextLayerId = 0;

    function currentPage() {
        return pages[currentPageIndex];
    }

    function serializeCanvas() {
        return canvas.toObject(['presetAssetId', 'dynamicPageNumber']);
    }

    function storeCurrentPage() {
        currentPage().canvas = serializeCanvas();
        currentPage().title = `Halaman ${currentPageIndex + 1}`;
    }

    function setStatus(message, isError = false) {
        status.textContent = message;
        status.classList.toggle('text-red-600', isError);
        status.classList.toggle('dark:text-red-300', isError);
    }

    function snapshot() {
        if (restoringCanvas) {
            return;
        }

        const serialized = JSON.stringify(serializeCanvas());
        if (history[historyIndex] === serialized) {
            return;
        }

        history.splice(historyIndex + 1);
        history.push(serialized);
        historyIndex = history.length - 1;
        setStatus('Ada perubahan yang belum disimpan.');
        updateHistoryButtons();
        scheduleSave();
    }

    function updateHistoryButtons() {
        root.querySelector('[data-action="undo"]').disabled = historyIndex <= 0;
        root.querySelector('[data-action="redo"]').disabled = historyIndex >= history.length - 1;
    }

    function updatePageControls() {
        pageIndicator.textContent = `Halaman ${currentPageIndex + 1} dari ${pages.length}`;
        root.querySelector('[data-action="previous-page"]').disabled = currentPageIndex === 0;
        root.querySelector('[data-action="next-page"]').disabled = currentPageIndex === pages.length - 1;
        root.querySelector('[data-action="add-page"]').disabled = pages.length >= 25;
        root.querySelector('[data-action="remove-page"]').disabled = pages.length === 1;
    }

    function refreshLayers() {
        layerList.replaceChildren();
        layerObjects.clear();
        const selectedObject = canvas.getActiveObject();
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Pilih elemen';
        layerSelect.replaceChildren(placeholder);

        const objects = [...canvas.getObjects()].reverse();
        layerEmpty.classList.toggle('hidden', objects.length > 0);

        objects.forEach((object) => {
            const item = document.createElement('li');
            const button = document.createElement('button');
            const layerId = getLayerId(object);
            const label = isTextObject(object)
                ? (object.text || 'Teks')
                : String(object.type).toLowerCase() === 'image' ? 'Gambar' : object.type;
            const option = document.createElement('option');
            option.value = layerId;
            option.textContent = label;
            option.selected = selectedObject === object;
            layerSelect.append(option);
            layerObjects.set(layerId, object);
            button.type = 'button';
            button.className = 'w-full truncate rounded-lg border px-3 py-2 text-left text-xs hover:border-blue-400 dark:text-gray-200';
            button.classList.add(selectedObject === object ? 'border-blue-500' : 'border-gray-200');
            button.textContent = label;
            button.addEventListener('click', () => {
                canvas.setActiveObject(object);
                canvas.requestRenderAll();
                refreshLayers();
                updateProperties();
            });
            item.append(button);
            layerList.append(item);
        });
    }

    function getLayerId(object) {
        if (!layerIds.has(object)) {
            layerIds.set(object, `layer-${++nextLayerId}`);
        }

        return layerIds.get(object);
    }

    function updateProperties() {
        const selected = canvas.getActiveObject();
        const canEditText = isTextObject(selected);
        const textInput = root.querySelector('[data-property="text"]');
        const fontInput = root.querySelector('[data-property="fontFamily"]');
        const sizeInput = root.querySelector('[data-property="fontSize"]');
        const colorInput = root.querySelector('[data-property="fill"]');
        const alignInput = root.querySelector('[data-property="textAlign"]');
        const deleteButton = root.querySelector('[data-action="delete-object"]');

        textInput.disabled = !canEditText;
        fontInput.disabled = !canEditText;
        sizeInput.disabled = !canEditText;
        alignInput.disabled = !canEditText;
        colorInput.disabled = !selected;
        deleteButton.disabled = !selected;

        if (!selected) {
            textInput.value = '';
            return;
        }

        textInput.value = canEditText ? selected.text : '';
        fontInput.value = selected.fontFamily || 'Arial';
        sizeInput.value = selected.fontSize || 32;
        colorInput.value = isHexColor(selected.fill) ? selected.fill : '#111827';
        alignInput.value = selected.textAlign || 'left';
    }

    function updateComponentsFromForm() {
        return [...document.querySelectorAll('[data-component-toggle]:checked')]
            .map((input) => input.value)
            .filter((key, index, values) => componentKeys.includes(key) && values.indexOf(key) === index);
    }

    function fetchJson(url, options) {
        return fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...options.headers,
            },
        }).then(async (response) => {
            const body = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = Object.values(body.errors || {}).flat().join(' ') || body.message || 'Permintaan gagal diproses.';
                throw new Error(message);
            }

            return body;
        });
    }

    async function savePreset(showSavedStatus = true) {
        storeCurrentPage();
        const serialized = {
            version: 1,
            pages,
        };
        const payload = {
            name: root.querySelector('[data-preset-name-input]').value.trim(),
            components: updateComponentsFromForm(),
            canvas_data: JSON.stringify(serialized),
        };

        if (!payload.components.length) {
            throw new Error('Pilih setidaknya satu bagian dokumen.');
        }

        await fetchJson(root.dataset.saveUrl, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        if (showSavedStatus) {
            setStatus('Preset dan desain berhasil disimpan.');
        }
    }

    function scheduleSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            savePreset().catch((error) => setStatus(error.message, true));
        }, 1400);
    }

    async function loadPage(index, saveCurrent = true) {
        if (index < 0 || index >= pages.length) {
            return;
        }

        if (saveCurrent) {
            storeCurrentPage();
        }
        currentPageIndex = index;
        restoringCanvas = true;
        await canvas.loadFromJSON(currentPage().canvas || { objects: [] });
        canvas.setDimensions({ width: pageWidth, height: pageHeight });
        canvas.backgroundColor = '#ffffff';
        canvas.requestRenderAll();
        history.splice(0, history.length);
        historyIndex = -1;
        restoringCanvas = false;
        snapshot();
        updatePageControls();
        refreshLayers();
        updateProperties();
    }

    function addObject(object) {
        canvas.add(object);
        canvas.setActiveObject(object);
        canvas.requestRenderAll();
        refreshLayers();
        updateProperties();
    }

    function addText() {
        addObject(new Textbox('Klik dua kali untuk mengedit teks', {
            left: 80,
            top: 100,
            width: 520,
            fontFamily: 'Arial',
            fontSize: 32,
            fill: '#111827',
            editable: true,
        }));
    }

    function addRectangle() {
        addObject(new Rect({
            left: 100,
            top: 160,
            width: 240,
            height: 140,
            fill: '#dbeafe',
            stroke: '#2563eb',
            strokeWidth: 2,
            rx: 8,
            ry: 8,
        }));
    }

    function addCircle() {
        addObject(new Circle({
            left: 120,
            top: 180,
            radius: 70,
            fill: '#dcfce7',
            stroke: '#16a34a',
            strokeWidth: 2,
        }));
    }

    async function addImage(url, assetId, name) {
        const image = await FabricImage.fromURL(url, { crossOrigin: 'same-origin' });
        image.scaleToWidth(Math.min(image.width, 420));
        image.set({
            left: Math.max(20, (pageWidth - image.getScaledWidth()) / 2),
            top: Math.max(20, (pageHeight - image.getScaledHeight()) / 2),
        });
        image.presetAssetId = Number(assetId);
        image.assetName = name;
        addObject(image);
    }

    function addAssetButton(asset) {
        const card = document.createElement('div');
        const button = document.createElement('button');
        const image = document.createElement('img');
        const label = document.createElement('span');
        const deleteButton = document.createElement('button');

        card.dataset.assetCard = asset.id;
        card.className = 'overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700';
        button.type = 'button';
        button.dataset.assetId = asset.id;
        button.dataset.assetUrl = asset.url;
        button.dataset.assetName = asset.name;
        button.className = 'group block w-full text-left hover:border-blue-500';
        image.src = asset.url;
        image.alt = '';
        image.className = 'aspect-square w-full bg-gray-50 object-contain dark:bg-gray-900';
        label.className = 'block truncate px-2 py-1.5 text-[10px] text-gray-600 dark:text-gray-300';
        label.textContent = asset.name;
        button.append(image, label);
        deleteButton.type = 'button';
        deleteButton.dataset.deleteAsset = asset.id;
        deleteButton.className = 'w-full border-t border-gray-200 px-2 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-gray-700 dark:text-red-300 dark:hover:bg-red-950/30';
        deleteButton.textContent = 'Hapus gambar';
        card.append(button, deleteButton);
        assetList.prepend(card);
        bindAssetCard(card);
        assetsEmpty.classList.add('hidden');
    }

    function bindAssetCard(card) {
        const imageButton = card.querySelector('[data-asset-id]');
        const deleteButton = card.querySelector('[data-delete-asset]');

        imageButton.addEventListener('click', () => {
            addImage(imageButton.dataset.assetUrl, imageButton.dataset.assetId, imageButton.dataset.assetName)
                .catch((error) => setStatus(error.message, true));
        });
        deleteButton.addEventListener('click', () => {
            deleteAsset(imageButton.dataset.assetId, deleteButton).catch((error) => setStatus(error.message, true));
        });
    }

    async function deleteAsset(assetId, button) {
        if (!window.confirm('Hapus gambar ini dari preset? Gambar yang masih digunakan akan dihapus dari semua halaman.')) {
            return;
        }

        button.disabled = true;
        setStatus('Menghapus gambar dari desain...');
        clearTimeout(saveTimer);
        storeCurrentPage();
        const id = Number(assetId);

        pages.forEach((page) => {
            page.canvas.objects = page.canvas.objects.filter((object) => Number(object.presetAssetId) !== id);
        });

        restoringCanvas = true;
        canvas.getObjects()
            .filter((object) => Number(object.presetAssetId) === id)
            .forEach((object) => canvas.remove(object));
        restoringCanvas = false;
        storeCurrentPage();
        refreshLayers();
        updateProperties();

        try {
            await savePreset(false);
            await fetchJson(root.dataset.assetDeleteUrl.replace('__FILE_ID__', encodeURIComponent(assetId)), {
                method: 'DELETE',
            });
            button.closest('[data-asset-card]').remove();
            assetsEmpty.classList.toggle('hidden', assetList.children.length > 0);
            setStatus('Gambar berhasil dihapus.');
        } catch (error) {
            button.disabled = false;
            setStatus(error.message, true);
        }
    }

    function applyZoom(value, point = new Point(pageWidth / 2, pageHeight / 2)) {
        zoomScale = Math.min(2, Math.max(0.5, value));
        canvas.zoomToPoint(point, zoomScale);
        canvas.requestRenderAll();
        root.querySelector('[data-zoom-label]').textContent = `${Math.round(zoomScale * 100)}%`;
    }

    canvas.on('selection:created', () => {
        refreshLayers();
        updateProperties();
    });
    canvas.on('selection:updated', () => {
        refreshLayers();
        updateProperties();
    });
    canvas.on('selection:cleared', () => {
        refreshLayers();
        updateProperties();
    });
    canvas.on('object:added', snapshot);
    canvas.on('object:modified', snapshot);
    canvas.on('object:removed', snapshot);
    canvas.on('text:changed', snapshot);

    root.querySelectorAll('[data-action]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                switch (button.dataset.action) {
                    case 'add-text':
                        addText();
                        break;
                    case 'add-rect':
                        addRectangle();
                        break;
                    case 'add-circle':
                        addCircle();
                        break;
                    case 'zoom-in':
                        applyZoom(zoomScale + 0.1);
                        break;
                    case 'zoom-out':
                        applyZoom(zoomScale - 0.1);
                        break;
                    case 'zoom-reset':
                        applyZoom(1);
                        break;
                    case 'delete-object': {
                        const selected = canvas.getActiveObject();
                        if (selected) {
                            canvas.remove(selected);
                            canvas.discardActiveObject();
                            canvas.requestRenderAll();
                            refreshLayers();
                            updateProperties();
                        }
                        break;
                    }
                    case 'layer-up': {
                        const selected = canvas.getActiveObject();
                        if (selected) {
                            canvas.bringObjectForward(selected);
                            canvas.requestRenderAll();
                            refreshLayers();
                            snapshot();
                        }
                        break;
                    }
                    case 'layer-down': {
                        const selected = canvas.getActiveObject();
                        if (selected) {
                            canvas.sendObjectBackwards(selected);
                            canvas.requestRenderAll();
                            refreshLayers();
                            snapshot();
                        }
                        break;
                    }
                    case 'previous-page':
                        await loadPage(currentPageIndex - 1);
                        break;
                    case 'next-page':
                        await loadPage(currentPageIndex + 1);
                        break;
                    case 'add-page':
                        if (pages.length >= 25) {
                            break;
                        }
                        storeCurrentPage();
                        pages.push(createPage(pages.length + 1));
                        await loadPage(pages.length - 1);
                        break;
                    case 'remove-page':
                        if (pages.length > 1 && window.confirm('Hapus halaman ini beserta semua elemennya?')) {
                            pages.splice(currentPageIndex, 1);
                            currentPageIndex = Math.min(currentPageIndex, pages.length - 1);
                            await loadPage(currentPageIndex, false);
                            scheduleSave();
                        }
                        break;
                    case 'undo':
                        await restoreHistory(historyIndex - 1);
                        break;
                    case 'redo':
                        await restoreHistory(historyIndex + 1);
                        break;
                    case 'save':
                        clearTimeout(saveTimer);
                        await savePreset();
                        break;
                    case 'export':
                        clearTimeout(saveTimer);
                        await savePreset(false);
                        await exportPage();
                        break;
                }
            } catch (error) {
                setStatus(error.message, true);
            }
        });
    });

    async function restoreHistory(index) {
        if (index < 0 || index >= history.length) {
            return;
        }

        restoringCanvas = true;
        historyIndex = index;
        await canvas.loadFromJSON(JSON.parse(history[historyIndex]));
        canvas.requestRenderAll();
        restoringCanvas = false;
        storeCurrentPage();
        updateHistoryButtons();
        refreshLayers();
        updateProperties();
        setStatus('Ada perubahan yang belum disimpan.');
        scheduleSave();
    }

    root.querySelectorAll('[data-property]').forEach((input) => {
        input.addEventListener(input.type === 'text' || input.tagName === 'TEXTAREA' ? 'input' : 'change', () => {
            const selected = canvas.getActiveObject();
            if (!selected) {
                return;
            }

            const property = input.dataset.property;
            const value = property === 'fontSize' ? Number(input.value) : input.value;
            if (property === 'text' && isTextObject(selected)) {
                selected.set('text', value);
            } else {
                selected.set(property, value);
            }

            selected.setCoords();
            canvas.requestRenderAll();
            snapshot();
        });
    });

    root.querySelectorAll('[data-format]').forEach((button) => {
        button.addEventListener('click', () => {
            const selected = canvas.getActiveObject();
            if (!selected || !isTextObject(selected)) {
                return;
            }

            const format = button.dataset.format;
            if (format === 'bold') {
                selected.set('fontWeight', selected.fontWeight === 'bold' ? 'normal' : 'bold');
            } else if (format === 'italic') {
                selected.set('fontStyle', selected.fontStyle === 'italic' ? 'normal' : 'italic');
            } else if (format === 'underline') {
                selected.set('underline', !selected.underline);
            }

            canvas.requestRenderAll();
            snapshot();
            updateProperties();
        });
    });

    root.querySelector('[data-upload-input]').addEventListener('change', async (event) => {
        const file = event.target.files[0];
        event.target.value = '';
        if (!file) {
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        setStatus('Mengunggah gambar...');

        try {
            const asset = await fetchJson(root.dataset.uploadUrl, {
                method: 'POST',
                body: formData,
            });
            addAssetButton(asset);
            await addImage(asset.url, asset.id, asset.name);
            setStatus('Gambar diunggah dan ditambahkan ke canvas.');
        } catch (error) {
            setStatus(error.message, true);
        }
    });

    assetList.querySelectorAll('[data-asset-card]').forEach(bindAssetCard);
    layerSelect.addEventListener('change', () => {
        const selected = layerObjects.get(layerSelect.value);
        if (!selected) {
            canvas.discardActiveObject();
            canvas.requestRenderAll();
            refreshLayers();
            updateProperties();
            return;
        }

        canvas.setActiveObject(selected);
        canvas.requestRenderAll();
        refreshLayers();
        updateProperties();
    });

    const canvasScroll = root.querySelector('.document-canvas-scroll');
    let pinchStartDistance = 0;
    let pinchStartZoom = zoomScale;
    canvasScroll.addEventListener('touchstart', (event) => {
        if (event.touches.length === 2) {
            event.preventDefault();
            pinchStartDistance = touchDistance(event.touches);
            pinchStartZoom = zoomScale;
        }
    }, { passive: false });
    canvasScroll.addEventListener('touchmove', (event) => {
        if (event.touches.length === 2 && pinchStartDistance > 0) {
            event.preventDefault();
            const centerX = (event.touches[0].clientX + event.touches[1].clientX) / 2;
            const centerY = (event.touches[0].clientY + event.touches[1].clientY) / 2;
            const canvasRect = canvas.upperCanvasEl.getBoundingClientRect();
            const point = new Point(
                (centerX - canvasRect.left) * pageWidth / canvasRect.width,
                (centerY - canvasRect.top) * pageHeight / canvasRect.height,
            );
            applyZoom(pinchStartZoom * touchDistance(event.touches) / pinchStartDistance, point);
        }
    }, { passive: false });
    canvasScroll.addEventListener('touchend', (event) => {
        if (event.touches.length < 2) {
            pinchStartDistance = 0;
        }
    });

    document.querySelectorAll('[data-component-toggle]').forEach((input) => {
        input.addEventListener('change', () => {
            clearTimeout(saveTimer);
            savePreset().catch((error) => setStatus(error.message, true));
        });
    });

    root.querySelector('[data-preset-name-input]').addEventListener('change', () => {
        clearTimeout(saveTimer);
        savePreset().catch((error) => setStatus(error.message, true));
    });

    document.addEventListener('keydown', async (event) => {
        const isEditingText = canvas.getActiveObject()?.isEditing;
        const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);
        if (isEditingText || isInput) {
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            clearTimeout(saveTimer);
            savePreset().catch((error) => setStatus(error.message, true));
        } else if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'z') {
            event.preventDefault();
            await restoreHistory(historyIndex - (event.shiftKey ? -1 : 1));
        } else if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'y') {
            event.preventDefault();
            await restoreHistory(historyIndex + 1);
        } else if (['Delete', 'Backspace'].includes(event.key) && canvas.getActiveObject()) {
            canvas.remove(canvas.getActiveObject());
            canvas.discardActiveObject();
            canvas.requestRenderAll();
            refreshLayers();
            updateProperties();
        }
    });

    async function exportPage() {
        setStatus('Membuat PNG...');
        const dynamicPageNumbers = canvas.getObjects()
            .filter((object) => object.dynamicPageNumber)
            .map((object) => ({ object, text: object.text }));
        let dataUrl;
        const viewportTransform = [...canvas.viewportTransform];
        try {
            dynamicPageNumbers.forEach(({ object }) => {
                object.set('text', `Halaman ${currentPageIndex + 1}`);
            });
            canvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
            canvas.requestRenderAll();
            dataUrl = canvas.toDataURL({ format: 'png', multiplier: 1 });
        } finally {
            dynamicPageNumbers.forEach(({ object, text }) => object.set('text', text));
            canvas.setViewportTransform(viewportTransform);
            canvas.requestRenderAll();
        }
        const response = await fetch(dataUrl);
        const blob = await response.blob();
        const formData = new FormData();
        formData.append('file', blob, `halaman-${currentPageIndex + 1}.png`);
        formData.append('page', String(currentPageIndex + 1));
        const exported = await fetchJson(root.dataset.exportUrl, {
            method: 'POST',
            body: formData,
        });

        updateExportPreview(exported.url);
        const download = document.createElement('a');
        download.href = exported.url;
        download.download = exported.name;
        download.click();
        setStatus('PNG berhasil disimpan dan diunduh.');
    }

    function updateExportPreview(url) {
        let preview = document.querySelector('[data-export-preview]');
        if (!preview) {
            const previewContainer = document.querySelector('[data-export-preview-container]');
            if (!previewContainer) {
                return;
            }

            previewContainer.replaceChildren();
            preview = document.createElement('img');
            preview.dataset.exportPreview = '';
            preview.alt = 'Preview hasil PNG';
            preview.className = 'max-h-64 w-auto rounded border border-gray-200 bg-white object-contain shadow-sm dark:border-gray-700';
            previewContainer.append(preview);
        }

        preview.src = url;
    }

    async function initialize() {
        restoringCanvas = true;
        await canvas.loadFromJSON(currentPage().canvas || { objects: [] });
        canvas.setDimensions({ width: pageWidth, height: pageHeight });
        canvas.backgroundColor = '#ffffff';
        canvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
        zoomScale = 1;
        root.querySelector('[data-zoom-label]').textContent = '100%';
        canvas.requestRenderAll();
        restoringCanvas = false;
        updatePageControls();
        refreshLayers();
        updateProperties();
        updateHistoryButtons();
        history.push(JSON.stringify(serializeCanvas()));
        historyIndex = 0;
        storeCurrentPage();
        setStatus('Semua perubahan tersimpan.');
    }

    initialize().catch((error) => setStatus(`Canvas gagal dimuat: ${error.message}`, true));
}

function touchDistance(touches) {
    const horizontal = touches[0].clientX - touches[1].clientX;
    const vertical = touches[0].clientY - touches[1].clientY;

    return Math.hypot(horizontal, vertical);
}

function renderTemplateFields(templateId, container, schoolName) {
    container.replaceChildren();

    templateFieldsFor(templateId, schoolName).forEach((field) => {
        const wrapper = document.createElement('div');
        const label = document.createElement('label');
        const input = document.createElement(field.type === 'asset'
            ? 'select'
            : field.type === 'textarea' ? 'textarea' : 'input');
        const fieldId = `template-field-${field.name}`;

        wrapper.className = field.type === 'textarea' ? 'sm:col-span-2' : '';
        label.htmlFor = fieldId;
        label.className = 'block text-xs font-medium text-gray-700 dark:text-gray-300';
        label.textContent = field.label;
        input.id = fieldId;
        input.name = field.name;
        input.dataset.templateField = field.type === 'asset' ? 'asset' : field.name;
        input.className = 'mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white';

        if (field.type === 'asset') {
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = 'Tidak menggunakan gambar';
            input.append(emptyOption);

            const assetList = document.querySelector('[data-asset-list]');
            assetList?.querySelectorAll('[data-asset-id]').forEach((assetButton) => {
                const option = document.createElement('option');
                option.value = assetButton.dataset.assetId;
                option.textContent = assetButton.dataset.assetName;
                input.append(option);
            });
        } else {
            if (field.type !== 'textarea') {
                input.type = 'text';
            }
            input.value = field.value || '';
            input.placeholder = field.placeholder || '';
            input.required = Boolean(field.required);
            if (field.type === 'textarea') {
                input.rows = 3;
            }
        }

        wrapper.append(label, input);
        container.append(wrapper);
    });
}

function templateFieldsFor(templateId, schoolName) {
    const text = (name, label, options = {}) => ({ name, label, type: 'text', ...options });
    const asset = (name, label) => ({ name, label, type: 'asset' });

    if (templateId.startsWith('cover-')) {
        return [
            text('schoolName', 'Nama sekolah', { value: schoolName, required: true }),
            text('title', 'Judul laporan', { placeholder: 'Contoh: Laporan Kegiatan Adiwiyata', required: true }),
            text('date', 'Tanggal verifikasi dokumen', { value: '[Tanggal verifikasi dokumen]' }),
            text('subtitle', 'Subjudul / tema lomba', { placeholder: 'Tema atau nama perlombaan' }),
            asset('logo', 'Logo sekolah'),
        ];
    }

    if (templateId.startsWith('header-')) {
        const logoCount = templateId === 'header-two-logos' ? 2 : templateId === 'header-three-logos' ? 3 : 4;
        return [
            text('schoolName', 'Nama sekolah', { value: schoolName, required: true }),
            text('address', 'Alamat lengkap'),
            text('street', 'Jalan / wilayah'),
            text('contact', 'Kontak sekolah'),
            ...Array.from({ length: logoCount }, (_, index) => asset(`logo${index + 1}`, `Logo ${index + 1}`)),
        ];
    }

    if (templateId.startsWith('intro-') || templateId.startsWith('findings-')) {
        const isFinding = templateId.startsWith('findings-');
        return [
            text('heading', 'Judul bagian', { value: isFinding ? 'Masalah yang Ditemukan' : 'Pendahuluan', required: true }),
            { name: 'paragraph', label: isFinding ? 'Uraian masalah' : 'Paragraf pendahuluan', type: 'textarea', required: true },
            asset('image', 'Gambar (opsional)'),
            text('caption', 'Keterangan gambar'),
        ];
    }

    if (templateId.startsWith('appendix-')) {
        const imageCount = templateId === 'appendix-grid' ? 4 : 1;
        return [
            text('heading', 'Judul lampiran', { value: 'Lampiran', required: true }),
            text('description', 'Penjelasan lampiran'),
            ...Array.from({ length: imageCount }, (_, index) => [
                asset(`image${index + 1}`, imageCount === 1 ? 'Gambar lampiran' : `Gambar ${index + 1}`),
                text(`caption${index + 1}`, `Keterangan gambar ${index + 1}`),
            ]).flat(),
        ];
    }

    if (templateId.startsWith('footer-')) {
        return [text('footerText', 'Teks footer', { value: 'Dokumen resmi sekolah' })];
    }

    if (templateId.startsWith('signature-')) {
        const fields = [
            text('cityDate', 'Tempat dan tanggal'),
            text('signerTitle', 'Jabatan penanda tangan', { value: 'Kepala Sekolah', required: true }),
            asset('signatureImage', 'Gambar tanda tangan / stempel'),
            text('signerName', 'Nama terang', { required: true }),
            text('signerNumber', 'NIP / nomor identitas'),
        ];
        if (templateId === 'signature-dual') {
            fields.push(
                text('secondSignerTitle', 'Jabatan penanda tangan kedua'),
                asset('secondSignatureImage', 'Tanda tangan kedua'),
                text('secondSignerName', 'Nama penanda tangan kedua'),
                text('secondSignerNumber', 'NIP / nomor identitas kedua'),
            );
        }

        return fields;
    }

    return [];
}

async function createTemplateObjects(templateId, values, assetList) {
    const objects = [];
    const navy = '#193b63';
    const accent = templateId.endsWith('modern') || templateId.endsWith('editorial') ? '#a66b35' : navy;
    const muted = '#64748b';
    const ink = '#1f2937';
    const section = templateId.startsWith('cover-') ? 'cover'
        : templateId.startsWith('header-') ? 'header'
            : templateId.startsWith('footer-') ? 'footer'
                : templateId.startsWith('signature-') ? 'signature'
                    : 'body';

    const addText = (content, left, top, width, fontSize, options = {}) => {
        objects.push(new Textbox(content || '', {
            left,
            top,
            width,
            fontFamily: options.fontFamily || 'Arial',
            fontSize,
            fill: options.fill || ink,
            fontWeight: options.fontWeight || 'normal',
            textAlign: options.textAlign || 'left',
            lineHeight: options.lineHeight || 1.4,
            editable: true,
        }));
    };
    const addRule = (left, top, width, color = accent, height = 2) => {
        objects.push(new Rect({ left, top, width, height, fill: color, stroke: color, strokeWidth: 0 }));
    };
    const addImageOrPlaceholder = async (assetId, left, top, width, height, placeholder) => {
        const assetButton = assetList.querySelector(`[data-asset-id="${CSS.escape(String(assetId || ''))}"]`);
        if (!assetId || !assetButton) {
            objects.push(new Rect({
                left,
                top,
                width,
                height,
                fill: '#f8fafc',
                stroke: '#cbd5e1',
                strokeWidth: 1,
            }));
            addText(placeholder, left + 6, top + height / 2 - 12, width - 12, 14, { fill: muted, textAlign: 'center' });
            return;
        }

        const image = await FabricImage.fromURL(assetButton.dataset.assetUrl, { crossOrigin: 'same-origin' });
        const scale = Math.min(width / image.width, height / image.height);
        image.set({
            left: left + (width - image.width * scale) / 2,
            top: top + (height - image.height * scale) / 2,
            scaleX: scale,
            scaleY: scale,
        });
        image.presetAssetId = Number(assetId);
        image.assetName = assetButton.dataset.assetName;
        objects.push(image);
    };

    if (section === 'cover') {
        if (templateId === 'cover-classic') {
            objects.push(new Rect({ left: 52, top: 52, width: 690, height: 1018, fill: 'transparent', stroke: navy, strokeWidth: 2 }));
            objects.push(new Rect({ left: 62, top: 62, width: 670, height: 998, fill: 'transparent', stroke: '#c8a86a', strokeWidth: 1 }));
        } else if (templateId === 'cover-modern') {
            addRule(0, 0, 14, navy, pageHeight);
        } else {
            addRule(120, 72, 554, '#c8a86a', 2);
        }

        await addImageOrPlaceholder(values.logo, 340, 112, 114, 114, 'Logo sekolah');
        addText(values.schoolName, 100, 258, 594, 32, { fill: navy, fontWeight: 'bold', textAlign: 'center' });
        addText('LAPORAN PERLOMBAAN', 105, 430, 584, 20, { fill: muted, fontWeight: 'bold', textAlign: 'center' });
        addText(values.title, 104, 480, 586, 38, {
            fill: navy,
            fontFamily: templateId === 'cover-journal' ? 'Georgia' : 'Arial',
            fontWeight: 'bold',
            textAlign: 'center',
        });
        addText(values.date, 112, 760, 570, 20, { fill: muted, textAlign: 'center' });
        addRule(222, 912, 350, templateId === 'cover-modern' ? '#c8a86a' : accent, 2);
        addText(values.subtitle, 100, 945, 594, 23, { fill: ink, fontWeight: 'bold', textAlign: 'center' });
    } else if (section === 'header') {
        const logoCount = templateId === 'header-two-logos' ? 2 : templateId === 'header-three-logos' ? 3 : 4;
        const logoWidth = logoCount === 4 ? 58 : 78;
        const gap = logoCount === 4 ? 32 : 46;
        const leftStart = (pageWidth - (logoCount * logoWidth + (logoCount - 1) * gap)) / 2;
        for (let index = 0; index < logoCount; index++) {
            await addImageOrPlaceholder(values[`logo${index + 1}`], leftStart + index * (logoWidth + gap), 42, logoWidth, 72, `Logo ${index + 1}`);
        }
        addText(values.schoolName, 90, 132, 614, 27, { fill: navy, fontWeight: 'bold', textAlign: 'center' });
        addText(values.address, 86, 169, 622, 16, { fill: ink, textAlign: 'center' });
        addText(values.street, 86, 192, 622, 15, { fill: muted, textAlign: 'center' });
        addText(values.contact, 86, 214, 622, 14, { fill: muted, textAlign: 'center' });
        addRule(62, 246, 670, navy, 3);
        addRule(62, 252, 670, '#c8a86a', 1);
    } else if (section === 'body') {
        const isAppendix = templateId.startsWith('appendix-');
        const isFindings = templateId.startsWith('findings-');
        const heading = values.heading || (isAppendix ? 'Lampiran' : isFindings ? 'Masalah yang Ditemukan' : 'Pendahuluan');
        addText(heading, 78, 82, 638, 27, {
            fill: accent,
            fontFamily: templateId.endsWith('editorial') ? 'Georgia' : 'Arial',
            fontWeight: 'bold',
        });
        addRule(78, 126, 638, accent, 2);

        if (templateId === 'appendix-split') {
            addText(values.description, 76, 182, 288, 23, { lineHeight: 1.55 });
            await addImageOrPlaceholder(values.image1, 420, 230, 300, 410, 'Gambar lampiran');
            addText(values.caption1, 420, 650, 300, 18, { fill: muted, textAlign: 'center' });
        } else if (templateId === 'appendix-full') {
            addText(values.description, 78, 151, 638, 18, { fill: muted, textAlign: 'center' });
            await addImageOrPlaceholder(values.image1, 92, 228, 610, 610, 'Gambar lampiran');
            addText(values.caption1, 82, 850, 630, 18, { fill: muted, textAlign: 'center' });
        } else if (templateId === 'appendix-grid') {
            const positions = [[78, 160], [414, 160], [78, 584], [414, 584]];
            for (let index = 0; index < positions.length; index++) {
                const [left, top] = positions[index];
                await addImageOrPlaceholder(values[`image${index + 1}`], left, top, 300, 300, `Gambar ${index + 1}`);
                addText(values[`caption${index + 1}`], left, top + 310, 300, 15, { fill: muted, textAlign: 'center' });
            }
        } else {
            addText(values.paragraph, 78, 164, 638, 25, {
                lineHeight: 1.6,
                fontFamily: templateId.endsWith('editorial') ? 'Georgia' : 'Arial',
            });
            if (values.image) {
                await addImageOrPlaceholder(values.image, 152, 540, 490, 260, 'Gambar pendukung');
                addText(values.caption, 100, 812, 594, 17, { fill: muted, textAlign: 'center' });
            }
        }
    } else if (section === 'footer') {
        addRule(72, 1026, 650, accent, templateId === 'footer-classic' ? 2 : 1);
        addText(values.footerText, 72, 1042, 360, 17, { fill: muted });
        const pageNumber = new Textbox('Halaman {nomor halaman}', {
            left: 520,
            top: 1042,
            width: 200,
            fontSize: 16,
            fill: accent,
            textAlign: 'right',
            editable: true,
        });
        pageNumber.dynamicPageNumber = true;
        objects.push(pageNumber);
    } else if (section === 'signature') {
        addText(values.cityDate, 410, 140, 300, 18, { textAlign: 'center' });
        const isDual = templateId === 'signature-dual';
        const signers = isDual
            ? [
                { left: 75, title: values.signerTitle, name: values.signerName, number: values.signerNumber, image: values.signatureImage },
                { left: 425, title: values.secondSignerTitle, name: values.secondSignerName, number: values.secondSignerNumber, image: values.secondSignatureImage },
            ]
            : [{ left: templateId === 'signature-center' ? 190 : 130, title: values.signerTitle, name: values.signerName, number: values.signerNumber, image: values.signatureImage }];
        const width = isDual ? 290 : 500;
        for (const signer of signers) {
            addText(signer.title, signer.left, 235, width, 18, { textAlign: 'center' });
            await addImageOrPlaceholder(signer.image, signer.left + (width - 210) / 2, 290, 210, 135, 'Tanda tangan / stempel');
            addRule(signer.left + 18, 468, width - 36, ink, 1);
            addText(signer.name, signer.left, 484, width, 18, { fontWeight: 'bold', textAlign: 'center' });
            addText(signer.number, signer.left, 508, width, 15, { fill: muted, textAlign: 'center' });
        }
    }

    return { section, objects };
}

function createPage(number) {
    return {
        id: `page-${Date.now()}-${number}`,
        title: `Halaman ${number}`,
        canvas: {
            version: '7.4.0',
            objects: [],
        },
    };
}

function parseBase64Json(value) {
    try {
        const binary = atob(value || '');
        const bytes = Uint8Array.from(binary, (character) => character.charCodeAt(0));
        return JSON.parse(new TextDecoder().decode(bytes));
    } catch {
        return { version: 1, pages: [createPage(1)] };
    }
}

function isHexColor(value) {
    return typeof value === 'string' && /^#[0-9a-f]{6}$/i.test(value);
}

function isTextObject(object) {
    return ['textbox', 'itext', 'text'].includes(String(object?.type).toLowerCase().replace(/[-_]/g, ''));
}
