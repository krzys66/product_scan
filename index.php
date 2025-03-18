<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan your product!</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/filebox.css">
</head>
<body>
    <header>
        <h1>Scan your product!</h1>
    </header>
    <main>
        <form action="scripts/process.php" method="post" enctype="multipart/form-data">
            <label for="product-photo-php">Upload photo of your product</label>
            <div class="upload-box">
                <label for="product-photo-php" class="upload-label">
                    <p>Browse Files</p>
                    <p class="sub-text">Drag and drop files here</p>
                    <p class="sub-text-phone">Take a photo of invoice</p>
                    <input name="product-photo-php" required type="file" id="product-photo" />
                </label>
                <p id="file-name"></p>
            </div>
            <button type="submit">Submit</button>
        </form>
    </main>
    <?php if (isset($_GET['Calories']) || isset($_GET['Protein']) || isset($_GET['Sugar'])): ?>
    <section id="results">
        <label>Results</label>
        <div id="results-container">
            <p>Calories: <?= htmlspecialchars($_GET['Calories']) ?> kcal</p>
            <p>Protein: <?= htmlspecialchars($_GET['Protein']) ?> g</p>
            <p>Sugar: <?= htmlspecialchars($_GET['Sugar']) ?> g</p>
        </div>
    </section>
    <?php endif; ?>
    <footer>
        <p>Created by krzys</p>
    </footer>
</body>
</html>
<script>
    document.getElementById("product-photo").addEventListener("change", function () {
        const fileName = this.files[0] ? this.files[0].name : "";
        document.getElementById("file-name").innerHTML = fileName;
    });
</script>