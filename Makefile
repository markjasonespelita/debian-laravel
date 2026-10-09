
.PHONY: all compile compile-app release clean

COMPILER := g++
QTFLAGS := $(shell pkg-config --cflags --libs Qt6Widgets Qt6WebEngineWidgets)

PACKAGE_NAME := zyphor
STAGING := debian
BUILD_DIR := binary

all: compile compile-app

# Generate the PHP CLI launcher
compile:
	php -r 'file_put_contents("caller", "#!/usr/bin/env php\n" . php_strip_whitespace("caller.php"));'
	chmod 755 caller

# Compile the Qt WebView application
compile-app:
	@pkg-config --exists Qt6Widgets Qt6WebEngineWidgets || \
		(echo "Missing Qt development dependencies."; \
		 echo "Install: sudo apt install qt6-base-dev qt6-webengine-dev pkg-config"; exit 1)
	mkdir -p $(BUILD_DIR)/usr/bin
	$(COMPILER) app.cpp -o $(BUILD_DIR)/usr/bin/binary $(QTFLAGS)
	$(COMPILER) app.cpp -o app $(QTFLAGS)

# Prepare the Debian package and build the .deb
release: all
	mkdir -p $(STAGING)/usr/bin
	mkdir -p $(STAGING)/usr/lib/$(PACKAGE_NAME)

	install -m 755 caller $(STAGING)/usr/bin/caller
	install -m 755 $(BUILD_DIR)/usr/bin/binary $(STAGING)/usr/bin/binary

	install -m 755 scripts/install-root \
		$(STAGING)/usr/lib/$(PACKAGE_NAME)/install-root

	chmod 755 $(STAGING)/DEBIAN/postinst
	chown -R root:root $(STAGING)/usr/bin $(STAGING)/usr/lib

	mkdir -p dist
	dpkg-deb --build $(STAGING) dist/$(PACKAGE_NAME).deb

	@echo "Release package created: dist/$(PACKAGE_NAME).deb"

# Remove generated build files and release artifacts
clean:
	rm -rf $(BUILD_DIR)
	rm -f app caller
	rm -rf dist
	rm -f $(STAGING)/usr/bin/caller
	rm -f $(STAGING)/usr/bin/binary
	rm -rf $(STAGING)/usr/lib/$(PACKAGE_NAME)