$files = Get-ChildItem -Path 'app\Services', 'app\Controllers', 'app\Models' -Filter *.php -Recurse

foreach ($file in $files) {
    # Run php-cs-fixer
    php php-cs-fixer.phar fix $file.FullName --config=.php-cs-fixer.php --quiet

    # Check if there are changes
    $status = git status --porcelain $file.FullName
    if ($status) {
        git add $file.FullName
        $fileName = $file.Name
        git commit -m "style: format and clean up $fileName"
    }
}
