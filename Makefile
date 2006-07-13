# $Id: Makefile,v 1.5 2004/11/25 20:18:39 x99laine Exp $
################################################################################
# definitions

VERSION := $(shell grep VERSION ChangeLog | head -1 | sed -e "s/VERSION //;s/\t.*//;s/ .*//")

PKG_NAME = platal
PKG_DIST = $(PKG_NAME)-$(VERSION)
PKG_FILES = AUTHORS ChangeLog COPYING README Makefile
PKG_DIRS = configs htdocs include install.d plugins po scripts templates upgrade

VCS_FILTER = ! -name .arch-ids ! -name CVS

################################################################################
# global targets

all: build

build: spool/templates_c wiki include/platal/globals.inc.php banana

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

banana: htdocs/images/banana htdocs/css/banana.css
htdocs/images/banana:
	cd $(@D) && ln -sf /usr/share/banana/img $(@F)

htdocs/css/banana.css:
	cd $(@D) && ln -sf /usr/share/banana/css/style.css $(@F)

build-wiki: wiki/local/farmconfig.php wiki/pub/skins/empty spool/wiki.d

wiki: get-wiki build-wiki spool/uploads htdocs/uploads htdocs/wiki wiki/cookbook/e-protect.php

################################################################################

.PHONY: build dist clean wiki build-wiki banana

