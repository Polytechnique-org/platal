# $Id: Makefile,v 1.4 2004-11-24 10:12:46 x2000habouzit Exp $
################################################################################
# definitions

VERSION := $(shell grep VERSION ChangeLog | head -1 | sed -e "s/VERSION //;s/\t.*//")

PKG_NAME = platal
PKG_DIST = $(PKG_NAME)-$(VERSION)
PKG_FILES = AUTHORS ChangeLog COPYING README Makefile
PKG_DIRS = configs htdocs include install.d plugins po scripts templates upgrade

VCS_FILTER = ! -name .arch-ids ! -name CVS

################################################################################
# global targets

build: pkg-build 

dist: clean pkg-dist

clean:
	rm -rf locale include/xorg.globals.inc.php

%: %.in Makefile
	sed -e 's,@VERSION@,$(VERSION),g' $< > $@

################################################################################
# devel targets
cache:
	mkdir cache
	chmod o+w cache

templates_c:
	mkdir templates_c
	chmod o+w templates_c

devel: build cache templates_c
	ln -sf ../cache/valid.html htdocs/valid.html

################################################################################
# diogenes package targets

pkg-build: include/xorg.globals.inc.php
#	make -C po

pkg-dist: pkg-build
	rm -rf $(PKG_DIST) $(PKG_DIST).tar.gz
	mkdir $(PKG_DIST)
	cp -a $(PKG_FILES) $(PKG_DIST)
	for dir in `find $(PKG_DIRS) -type d $(VCS_FILTER)`; \
	do \
          mkdir -p $(PKG_DIST)/$$dir; \
	  find $$dir -type f -maxdepth 1 -exec cp {} $(PKG_DIST)/$$dir \; ; \
	done
	tar czf $(PKG_DIST).tar.gz $(PKG_DIST)
	rm -rf $(PKG_DIST)


.PHONY: build dist clean pkg-build pkg-dist

