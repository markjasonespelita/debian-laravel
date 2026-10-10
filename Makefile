.PHONY: all compile compile-app release-app release-cli clean add

COMPILER := g++
QTFLAGS := $(shell pkg-config --cflags --libs Qt6Widgets Qt6WebEngineWidgets)

PACKAGE_NAME := zyphor
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

	# Copy scripts
	mkdir -p $(STAGING)/usr/lib/$(PACKAGE_NAME)/scripts
	cp -a scripts/. $(STAGING)/usr/lib/$(PACKAGE_NAME)/scripts/

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

	# Copy scripts
	mkdir -p $(STAGING)/usr/lib/$(PACKAGE_NAME)/scripts
	cp -a scripts/. $(STAGING)/usr/lib/$(PACKAGE_NAME)/scripts/

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

	git add app.cpp
	git commit -m "chore: update C++ application source"

	git add application/routes/console.php
	git commit -m "chore: update Laravel console routes"

	git add caller.php
	git commit -m "chore: update PHP caller script"

	git add CONFIG
	git commit -m "chore: add application configuration"

	git add application/app/Helpers/
	git commit -m "feat: add application helper classes"

	git add caller
	git commit -m "chore: add caller executable"

	git add debian/usr/
	git commit -m "chore: update Debian package files"

	git add dist/
	git commit -m "build: add distribution build files"

	git add helpers/
	git commit -m "feat: add helper utilities"

	git add scripts/sample.sh
	git commit -m "feat: add sample script"

	git add scripts/updater.sh
	git commit -m "feat: add updater script"