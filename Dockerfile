FROM php:8.2-cli

# Install required PHP extensions (including MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Copy project files into the container
WORKDIR /app
COPY . /app

# Expose the port that Render will use
EXPOSE 10000

# Start the PHP server using your custom router
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} router.php"]
