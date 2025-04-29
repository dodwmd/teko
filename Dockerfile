FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    python3 \
    python3-pip \
    python3-dev \
    python3-venv \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for Laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy code to working directory
COPY --chown=www:www . /var/www

# Install PHP dependencies
RUN composer install --no-interaction --no-plugins --no-scripts

# Install Python dependencies
RUN if [ -f /var/www/agents/requirements.txt ]; then \
    python3 -m venv /var/www/agents/.venv && \
    /var/www/agents/.venv/bin/pip install --upgrade pip && \
    /var/www/agents/.venv/bin/pip install -r /var/www/agents/requirements.txt; \
fi

# Generate Laravel key
RUN php artisan key:generate

# Set permissions
RUN chown -R www:www /var/www
RUN chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
