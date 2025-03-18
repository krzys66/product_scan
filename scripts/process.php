<?php

function resizeImage($sourcePath, $targetPath, $maxWidth = 4000, $maxHeight = 4000) {
    list($origWidth, $origHeight, $imageType) = getimagesize($sourcePath);

    if ($origWidth <= $maxWidth && $origHeight <= $maxHeight) {
        return copy($sourcePath, $targetPath);
    }

    $scale = min($maxWidth / $origWidth, $maxHeight / $origHeight);
    $newWidth = (int) ($origWidth * $scale);
    $newHeight = (int) ($origHeight * $scale);

    $image = imagecreatefromjpeg($sourcePath);
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    imagejpeg($newImage, $targetPath, 90);

    imagedestroy($image);
    imagedestroy($newImage);
}

function analyzeImage($imagePath) {
    $subscriptionKey = "6rpsJj314Z9tGHQmX7AuI2W6hsloJ68hvCyG82IUgDWLT0lmsDT4JQQJ99BCAC5RqLJXJ3w3AAAFACOGNsv4";
    $endpoint = "https://invoiceprocessingusingocr.cognitiveservices.azure.com/";

    $url = $endpoint . "/vision/v3.2/ocr?language=pl&detectOrientation=true";

    list($width, $height) = getimagesize($imagePath);
    if ($width < 50 || $height < 50 || $width > 4200 || $height > 4200) {
        $scaledPath = "scaled_image.jpg";
        resizeImage($imagePath, $scaledPath);
        $imagePath = $scaledPath;
    }

    $imageData = file_get_contents($imagePath);

    $headers = [
        "Content-Type: application/octet-stream",
        "Ocp-Apim-Subscription-Key: $subscriptionKey"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result["error"])) {
        die("Error API Azure: " . $result["error"]["message"]);
    }

    file_put_contents("ocr_result.json", json_encode($result, JSON_PRETTY_PRINT));

    return extractNutritionalInfo($result);
}

function extractNutritionalInfo($ocrResult) {
    $textArray = [];

    foreach ($ocrResult["regions"] as $region) {
        foreach ($region["lines"] as $line) {
            $lineText = implode(" ", array_column($line["words"], "text"));
            $textArray[] = trim($lineText);
        }
    }

    $text = implode("\n", $textArray);

    $patterns = [
        "calories" => "/(\d+[.,]?\d*)\s*(?:kcal|cal|kj|kJ)/i",
        "protein"  => "/(\d+[.,]?\d*)\s*(?:g|gram|grams|gramy|gramów|protein|białko|białka)/i",
        "sugar"    => "/(\d+[.,]?\d*)\s*(?:g|gram|grams|gramy|gramów|sugar|sugars|cukry|cukier)/i"
    ];

    $nutrients = [];

    foreach ($patterns as $key => $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $nutrients[$key] = str_replace(",", ".", $matches[1]);
        } else {
            $nutrients[$key] = "No data";
        }
    }

    return [
        "Calories" => $nutrients["calories"],
        "Protein"  => $nutrients["protein"],
        "Sugar"    => $nutrients["sugar"]
    ];
}


$target_dir = "../uploads/";
$original_name = basename($_FILES["product-photo-php"]["name"]);
$imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
$target_file = $target_dir . $original_name;

move_uploaded_file($_FILES["product-photo-php"]["tmp_name"], $target_file);

$nutrients = analyzeImage($target_file);

$results = [
    "Calories" => $nutrients["Calories"] ?? "No data",
    "Protein"  => $nutrients["Protein"] ?? "No data",
    "Sugar"    => $nutrients["Sugar"] ?? "No data"
];

unlink($target_file);
unlink("scaled_image.jpg");

header("Location: ../index.php?" . http_build_query($results));
exit();
?>
