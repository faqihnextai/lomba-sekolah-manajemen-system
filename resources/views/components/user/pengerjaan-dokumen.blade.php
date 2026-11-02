@props(['competition'])

<x-modul-lomba
    :judul="$competition['judul']"
    :deskripsi="$competition['deskripsi']"
    :warna="$competition['warna']"
    :babList="$competition['babList']"
/>