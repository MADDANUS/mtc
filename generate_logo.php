<?php
// Create a 200x200 transparent image
$img = imagecreatetruecolor(200, 200);
imagesavealpha($img, true);
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $transparent);

// Colors
$blue = imagecolorallocate($img, 0, 0, 255);
$white = imagecolorallocate($img, 255, 255, 255);
$lightBlue = imagecolorallocate($img, 0, 112, 192);

// Draw outer circle
imagearc($img, 100, 100, 180, 180, 0, 360, $blue);
imagearc($img, 100, 100, 178, 178, 0, 360, $blue);
imagearc($img, 100, 100, 170, 170, 0, 360, $blue);
imagearc($img, 100, 100, 168, 168, 0, 360, $blue);

// Add NSI text in the center
$font = 'C:\Windows\Fonts\arialbd.ttf'; // Use Arial Bold
if (file_exists($font)) {
    // Add white background for NSI text
    imagefilledrectangle($img, 50, 80, 150, 120, $white);
    imagettftext($img, 30, 0, 60, 115, $blue, $font, 'NSI');
} else {
    // Fallback if no font
    imagefilledrectangle($img, 50, 80, 150, 120, $white);
    imagestring($img, 5, 85, 90, 'NSI', $blue);
}

// Add tagline at the bottom of the image
if (file_exists($font)) {
    imagettftext($img, 14, 0, 50, 180, $lightBlue, $font, 'The Future');
    imagettftext($img, 14, 0, 45, 198, $lightBlue, $font, 'In Our Hands');
} else {
    imagestring($img, 3, 60, 170, 'The Future', $lightBlue);
    imagestring($img, 3, 55, 185, 'In Our Hands', $lightBlue);
}

// Ensure directory exists
if (!is_dir('public/uploads')) {
    mkdir('public/uploads', 0777, true);
}

// Save image
imagepng($img, 'public/uploads/nsi_logo.png');
imagedestroy($img);
echo "Logo generated successfully.";
?>
