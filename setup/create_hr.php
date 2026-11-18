<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CREATE HR ACCOUNT - CLI ONLY
|--------------------------------------------------------------------------
*/

if (PHP_SAPI !== 'cli') {

    http_response_code(403);

    exit('Akses ditolak. Jalankan file melalui terminal.');

}

require_once __DIR__ . '/../includes/db.php';


/*
|--------------------------------------------------------------------------
| INPUT HR
|--------------------------------------------------------------------------
*/

echo "\n";
echo "====================================\n";
echo "    ARTANA ATS - CREATE HR ACCOUNT  \n";
echo "====================================\n\n";


$fullName = trim(readline("Nama HR       : "));

$email = strtolower(trim(readline("Email HR      : ")));

$password = readline("Password HR   : ");

$confirmPassword = readline("Ulangi Password: ");


/*
|--------------------------------------------------------------------------
| VALIDASI
|--------------------------------------------------------------------------
*/

if (
    $fullName === '' ||
    strlen($fullName) > 150 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 190 ||
    strlen($password) < 8 ||
    $password !== $confirmPassword
) {

    exit("\nData tidak valid. Silakan jalankan kembali.\n");

}


/*
|--------------------------------------------------------------------------
| HASH PASSWORD
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| CEK EMAIL
|--------------------------------------------------------------------------
*/

$check = $pdo->prepare("
    SELECT id
    FROM users
    WHERE email = :email
");

$check->execute([
    'email' => $email
]);

if ($check->fetch()) {

    exit("\nEmail sudah terdaftar.\n");

}


/*
|--------------------------------------------------------------------------
| INSERT HR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    INSERT INTO users
    (
        full_name,
        email,
        password_hash,
        role,
        is_active
    )

    VALUES
    (
        :full_name,
        :email,
        :password_hash,
        'hr',
        1
    )
");

$stmt->execute([

    'full_name' => $fullName,

    'email' => $email,

    'password_hash' => $passwordHash

]);


echo "\n====================================\n";

echo "AKUN HR BERHASIL DIBUAT!\n";

echo "Nama  : " . $fullName . "\n";

echo "Email : " . $email . "\n";

echo "Role  : hr\n";

echo "====================================\n";

echo "\nSilakan login melalui website ARTANA ATS.\n";