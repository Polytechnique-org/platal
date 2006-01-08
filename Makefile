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

build: spool/templates_c wiki

clean:
	rm -rf include/platal/globals.inc.php

%: %.in Makefile
	sed -e 's,@VERSION@,$(VERSION),g' $< > $@

################################################################################
# targets

spool/templates_c spool/uploads:
	mkdir -p $@
	chmod o+w $@

spool/wiki.d:
	mkdir -p $@
	chmod o+w $@
	cd $@ && ln -sf ../../install.d/wiki/wiki.d/* .

wiki/cookbook/e-protect.php:
	cd wiki/cookbook && ln -sf ../../install.d/wiki/e-protect.php

htdocs/valid.html:
	touch spool/templates_c/valid.html
	chmod o+w spool/templates_c/valid.html
	cd htdocs && ln -sf ../spool/templates_c/valid.html

htdocs/uploads:
	cd htdocs && ln -sf ../spool/uploads

htdocs/wiki:
	cd htdocs && ln -sf ../wiki/pub wiki

wiki/local/farmconfig.php:
	cd wiki/local/     && ln -sf ../../plugins/pmwiki.config.php farmconfig.php

wiki/pub/skins/empty:
	cd wiki/pub/skins/ && ln -sf ../../../install.d/wiki/empty

get-wiki:
	@if ! test -d wiki; then                                          \
	    wget http://www.pmwiki.org/pub/pmwiki/pmwiki-latest.tgz;      \
	    tar -xzvf pmwiki-latest.tgz;				  \
	    rm pmwiki-latest.tgz;					  \
	    mv pmwiki-* wiki;						  \
	fi

build-wiki: wiki/local/farmconfig.php wiki/pub/skins/empty spool/wiki.d

wiki: get-wiki build-wiki spool/uploads htdocs/uploads htdocs/wiki wiki/cookbook/e-protect.php

################################################################################

.PHONY: build dist clean wiki build-wiki

