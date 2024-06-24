#!/bin/bash

# Create include directory if it doesn't exist
mkdir -p include

# Download Bootstrap CSS
echo "Downloading Bootstrap CSS..."
curl -o include/bootstrap.min.css https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css

# Download Font Awesome CSS
echo "Downloading Font Awesome CSS..."
curl -o include/fontawesome.min.css https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css

# Update contents.php to use local files
echo "Updating contents.php to use local files..."

sed -i.bak -e 's|https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css|include/bootstrap.min.css|' \
           -e 's|https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css|include/fontawesome.min.css|' contents.php

echo "Setup complete. contents.php has been updated to use local files."
