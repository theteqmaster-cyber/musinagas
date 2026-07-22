FROM php:8.2-cli-alpine

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Ensure required storage directories exist
RUN mkdir -p app/storage app/public/uploads

# Expose default port
EXPOSE 8080

# Run PHP built-in server bound to Render's PORT environment variable
CMD php -S 0.0.0.0:${PORT:-8080} -t app/public
