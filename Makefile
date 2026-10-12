.PHONY: all compile compile-app release-app release-cli clean add

COMPILER := g++
QTFLAGS := $(shell pkg-config --cflags --libs Qt6Widgets Qt6WebEngineWidgets)

PACKAGE_NAME := zyphors
STAGING := debian
DIST_DIR := dist

all: compile compile-app

# Generate the PHP CLI launcher
compile:
	php -r 'file_put_contents("caller", "#!/usr/bin/env php\n" . php_strip_whitespace("caller.php"));'
	chmod 755 caller

# Compile the Qt WebEngine application
compile-app:
	@pkg-config --exists Qt6Widgets Qt6WebEngineWidgets || \
		(echo "Missing Qt development dependencies."; \
		 echo "Install: sudo apt install qt6-base-dev qt6-webengine-dev pkg-config"; exit 1)
	mkdir -p $(STAGING)/usr/bin
	$(COMPILER) app.cpp -o $(STAGING)/usr/bin/$(PACKAGE_NAME) $(QTFLAGS)

# Release the Qt WebEngine application
release-app: compile-app
	mkdir -p $(DIST_DIR)

	# Remove stale CLI/application staging files
	rm -f $(STAGING)/usr/bin/$(PACKAGE_NAME)
	$(COMPILER) app.cpp -o $(STAGING)/usr/bin/$(PACKAGE_NAME) $(QTFLAGS)

	# Copy application files
	rm -rf $(STAGING)/usr/lib/$(PACKAGE_NAME)
	mkdir -p $(STAGING)/usr/lib/$(PACKAGE_NAME)
	cp -a application/. $(STAGING)/usr/lib/$(PACKAGE_NAME)/

	# Set post-installation script permissions
	chmod 755 $(STAGING)/DEBIAN/postinst

	# Build the application package
	dpkg-deb --build $(STAGING) $(DIST_DIR)/$(PACKAGE_NAME)-app.deb
	@echo "Application package created: $(DIST_DIR)/$(PACKAGE_NAME)-app.deb"

	cp $(STAGING)/usr/bin/$(PACKAGE_NAME) app

# Release the PHP CLI application
release-cli: compile
	mkdir -p $(DIST_DIR)

	# Remove stale Qt/application staging files
	rm -f $(STAGING)/usr/bin/$(PACKAGE_NAME)
	rm -rf $(STAGING)/usr/lib/$(PACKAGE_NAME)

	# Install PHP CLI launcher
	install -m 755 caller $(STAGING)/usr/bin/$(PACKAGE_NAME)

	# Copy application files
	mkdir -p $(STAGING)/usr/lib/$(PACKAGE_NAME)
	cp -a application/. $(STAGING)/usr/lib/$(PACKAGE_NAME)/

	# Set post-installation script permissions
	chmod 755 $(STAGING)/DEBIAN/postinst

	# Build the CLI package
	dpkg-deb --build $(STAGING) $(DIST_DIR)/$(PACKAGE_NAME).deb
	@echo "CLI package created: $(DIST_DIR)/$(PACKAGE_NAME).deb"

# Clean generated files while preserving Debian metadata
clean:
	rm -f app
	rm -f caller
	rm -f $(STAGING)/usr/bin/$(PACKAGE_NAME)
	rm -rf $(STAGING)/usr/lib/$(PACKAGE_NAME)
	rm -rf $(DIST_DIR)

add:
	git add Makefile
	git commit -m "chore: update Makefile"

	git add app
	git commit -m "chore: remove legacy app launcher"

	git add app.cpp
	git commit -m "chore: update C++ application source"

	git add application/index.php
	git commit -m "chore: update application entry point"

	git add application/scripts/install-root
	git commit -m "chore: remove application install-root script"

	git add application/scripts/sample.sh
	git commit -m "chore: remove sample script"

	git add application/scripts/updater.sh
	git commit -m "chore: remove application updater script"

	git add debian/DEBIAN/control
	git commit -m "chore: update Debian package control"

	git add debian/usr/bin/zyphors
	git commit -m "chore: remove zyphors executable"

	git add debian/usr/lib/zyphors/app.php
	git commit -m "chore: remove legacy zyphors app entry point"

	git add debian/usr/lib/zyphors/helpers/SystemHelper.php
	git commit -m "chore: remove legacy SystemHelper"

	git add debian/usr/lib/zyphors/index.php
	git commit -m "chore: remove legacy zyphors index"

	git add debian/usr/lib/zyphors/scripts/install-root
	git commit -m "chore: remove packaged install-root script"

	git add debian/usr/lib/zyphors/scripts/sample.sh
	git commit -m "chore: remove packaged sample script"

	git add debian/usr/lib/zyphors/scripts/updater.sh
	git commit -m "chore: remove packaged updater script"

	git add dist/zyphors-app.deb
	git commit -m "chore: remove outdated zyphors Debian package"

	git add application/backend/
	git commit -m "feat: add application backend"

	git add application/process/
	git commit -m "feat: add application process handlers"

	git add application/template.php
	git commit -m "feat: add application template"