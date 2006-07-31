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

build: core banana wiki

q:
	@echo -e "Code statistics\n"
	@sloccount $(filter-out wiki/ spool/, $(wildcard */)) 2> /dev/null | egrep '^[a-z]*:'

%: %.in Makefile
	sed -e 's,@VERSION@,$(VERSION),g' $< > $@

################################################################################
# targets

##
## core
##

core: spool/templates_c include/platal/globals.inc.php

spool/templates_c spool/uploads:
	mkdir -p $@
	chmod o+w $@


##
## wiki
##

WIKI_NEEDS = \
     wiki/local/farmconfig.php      \
     wiki/pub/skins/empty           \
     wiki/cookbook/e-protect.php    \
     spool/wiki.d                   \
     htdocs/uploads                 \
     htdocs/wiki                    \

wiki: get-wiki build-wiki

build-wiki: $(WIKI_NEEDS) | get-wiki

htdocs/uploads: spool/uploads
	cd htdocs && ln -sf ../spool/uploads

htdocs/wiki:
	cd htdocs && ln -sf ../wiki/pub wiki


spool/wiki.d:
	mkdir -p $@
	chmod o+w $@
	cd $@ && ln -sf ../../include/wiki/wiki.d/* .


wiki/cookbook/e-protect.php:
	cd wiki/cookbook && ln -sf ../../include/wiki/e-protect.php

wiki/local/farmconfig.php:
	cd wiki/local/ && ln -sf ../../include/wiki/farmconfig.php

wiki/pub/skins/empty:
	cd wiki/pub/skins/ && ln -sf ../../../include/wiki/empty


get-wiki:
	@if ! test -d wiki; then                                          \
	    wget http://www.pmwiki.org/pub/pmwiki/pmwiki-latest.tgz;      \
	    tar -xzvf pmwiki-latest.tgz;				  \
	    rm pmwiki-latest.tgz;					  \
	    mv pmwiki-* wiki;						  \
	fi

##
## banana
##

banana: htdocs/images/banana htdocs/css/banana.css
htdocs/images/banana:
	cd $(@D) && ln -sf /usr/share/banana/img $(@F)

htdocs/css/banana.css:
	cd $(@D) && ln -sf /usr/share/banana/css/style.css $(@F)

################################################################################

.PHONY: build dist clean wiki build-wiki banana

