<?php

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/*
==========================================
FORM VERİLERİ
==========================================
*/

$hizmet   = trim($_POST['hizmet'] ?? '');
$adSoyad  = trim($_POST['ad_soyad'] ?? '');
$firma    = trim($_POST['firma'] ?? '');
$telefon  = trim($_POST['telefon'] ?? '');
$email    = trim($_POST['email'] ?? '');
$mesaj    = trim($_POST['mesaj'] ?? '');
$onay     = $_POST['onay'] ?? '';
$website = trim($_POST['website'] ?? '');

if ($website !== '') {
    exit;
}

/*
==========================================
ZORUNLU ALAN KONTROLÜ
==========================================
*/

if (
    $hizmet === '' ||
    $adSoyad === '' ||
    $telefon === '' ||
    $email === '' ||
    $onay !== '1'
) {
    showResult(
        false,
        'Eksik Bilgi',
        'Lütfen zorunlu alanların tamamını doldurun.'
    );
}


/*
==========================================
E-POSTA KONTROLÜ
==========================================
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    showResult(
        false,
        'Geçersiz E-posta',
        'Lütfen geçerli bir e-posta adresi girin.'
    );
}


/*
==========================================
HEADER ENJEKSİYON KORUMASI
==========================================
*/

if (
    preg_match('/[\r\n]/', $email) ||
    preg_match('/[\r\n]/', $adSoyad)
) {
    showResult(
        false,
        'Geçersiz Talep',
        'Form bilgileri doğrulanamadı.'
    );
}


/*
==========================================
MAIL AYARLARI
==========================================
*/

$alici = 'ozkan@izguvenlik.com';

$konu = 'Yeni Fiyat Talebi - ' . $hizmet;


/*
==========================================
MAIL İÇERİĞİ
==========================================
*/

$mailIcerik = "
YENİ FİYAT TALEBİ
==============================

Hizmet:
{$hizmet}

Ad Soyad:
{$adSoyad}

Firma:
" . ($firma !== '' ? $firma : 'Belirtilmedi') . "

Telefon:
{$telefon}

E-posta:
{$email}

Mesaj:
" . ($mesaj !== '' ? $mesaj : 'Mesaj belirtilmedi') . "

==============================

Bu talep izguvenlik.com web sitesindeki
Fiyat Al formu üzerinden gönderilmiştir.
";


/*
==========================================
MAIL HEADER
==========================================
*/

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

/*
Burada ziyaretçinin mail adresini From olarak
kullanmıyoruz. Bazı sunucular bunu spam olarak
algılayabilir.

Gönderen alanı site domaininden olmalı.
*/

$headers .= "From: IZ Ozel Guvenlik <ozkan@izguvenlik.com>\r\n";
$headers .= "Reply-To: {$adSoyad} <{$email}>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();


/*
==========================================
MAIL GÖNDER
==========================================
*/

$gonderildi = mail(
    $alici,
    '=?UTF-8?B?' . base64_encode($konu) . '?=',
    $mailIcerik,
    $headers
);


if ($gonderildi) {

    showResult(
        true,
        'Talebiniz Alındı',
        'Fiyat talebiniz başarıyla iletildi. Ekibimiz sizinle en kısa sürede iletişime geçecektir.'
    );

} else {

    showResult(
        false,
        'Gönderim Başarısız',
        'Talebiniz şu anda gönderilemedi. Lütfen daha sonra tekrar deneyin veya bizimle telefon üzerinden iletişime geçin.'
    );
}


/*
==========================================
SONUÇ SAYFASI
==========================================
*/

function showResult($success, $title, $message)
{
    echo json_encode([
        'success' => $success,
        'title' => $title,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);

    exit;
}