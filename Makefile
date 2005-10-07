# $Id: Makefile,v 1.5 2004/11/25 20:18:39 x99laine Exp $
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

all: build

devel: build htdocs/valid.html

headers:
	headache -c install.d/platal-dev/templates/header.conf -h install.d/platal-dev/templates/header \
		`find templates -name '*.tpl' ! -path 'templates/xnet/skin.tpl' ! -path 'templates/skin/*.tpl' ! -name 'vcard.tpl' `

build: templates_c wiki

clean:
	rm -rf include/platal/globals.inc.php

%: %.in Makefile
	sed -e 's,@VERSION@,$(VERSION),g' $< > $@

################################################################################
# targets
templates_c uploads:
	mkdir -p $@
	chmod o+w $@

htdocs/valid.html:
	touch templates_c/valid.html
	cd htdocs && ln -sf ../templates_c/valid.html

htdocs/uploads:
	cd htdocs && ln -sf ../uploads

htdocs/wikipub:
	cd htdocs && ln -sf ../wiki/pub wikipub

wiki/local/pmwiki.config.php:
	cd wiki/local/     && ln -sf ../../plugins/pmwiki.config.php

wiki/pub/skins/empty:
	cd wiki/pub/skins/ && ln -sf ../../../install.d/wiki/empty

wiki: uploads htdocs/uploads htdocs/wikipub wiki/local/pmwiki.config.php wiki/pub/skins/empty
	@test -d wiki || wget http://www.pmwiki.org/pub/pmwiki/pmwiki-latest.tgz

################################################################################

.PHONY: build dist clean wiki

