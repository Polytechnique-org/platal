# $Id: Makefile,v 1.5 2004-11-25 20:18:39 x99laine Exp $
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

bzdist: clean pkg-bzdist

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

$(PKG_DIST): pkg-build
	mkdir $(PKG_DIST)
	cp -a $(PKG_FILES) $(PKG_DIST)
	for dir in `find $(PKG_DIRS) -type d $(VCS_FILTER)`; \
	do \
          mkdir -p $(PKG_DIST)/$$dir; \
	  find $$dir -type f -maxdepth 1 -exec cp {} $(PKG_DIST)/$$dir \; ; \
	done

pkg-dist: $(PKG_DIST)
	rm -f $(PKG_DIST).tar.gz
	tar czf $(PKG_DIST).tar.gz $(PKG_DIST)
	rm -rf $(PKG_DIST)

pkg-bzdist: $(PKG_DIST)
	rm -f $(PKG_DIST).tar.bz2
	tar cjf $(PKG_DIST).tar.bz2 $(PKG_DIST)
	rm -rf $(PKG_DIST)

.PHONY: build dist clean pkg-build pkg-dist

