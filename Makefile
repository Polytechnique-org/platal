
# $Id: Makefile,v 1.5 2004/11/25 20:18:39 x99laine Exp $
################################################################################
# definitions

VERSNUM := $(shell grep VERSION ChangeLog | head -1 | sed -e "s/VERSION //;s/ .*//")
VERSTAG := $(shell grep VERSION ChangeLog | head -1 | grep 'XX' > /dev/null 2> /dev/null && echo 'beta')
BANANA  := $(shell ( [ -d ../banana ] && echo `pwd`"/../banana" ) || echo "/home/web/dev/banana")

VERSION = $(VERSNUM)$(VERSTAG)

PKG_NAME = platal
PKG_DIST = $(PKG_NAME)-$(VERSION)
PKG_FILES = AUTHORS ChangeLog COPYING README Makefile
PKG_DIRS = configs htdocs include install.d plugins po scripts templates upgrade

VCS_FILTER = ! -name .arch-ids ! -name CVS

################################################################################
# global targets

all: build

build: core banana wiki jquery

q:
	@echo -e "Code statistics\n"
	@sloccount $(filter-out wiki/ spool/, $(wildcard */)) 2> /dev/null | egrep '^[a-z]*:'

%: %.in Makefile ChangeLog
	sed -e 's,@VERSION@,$(VERSION),g' $< > $@

################################################################################
# targets

##
## core
##

core: spool/templates_c spool/mails_c include/globals.inc.php configs/platal.cron htdocs/.htaccess spool/conf spool/tmp

spool/templates_c spool/mails_c spool/uploads spool/conf spool/tmp:
	mkdir -p $@
	chmod o+w $@

htdocs/.htaccess: htdocs/.htaccess.in Makefile
	@REWRITE_BASE="/~$$(id -un)"; \
	test "$$REWRITE_BASE" = "/~web" && REWRITE_BASE="/"; \
	sed -e "s,@REWRITE_BASE@,$$REWRITE_BASE,g" $< > $@

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

htdocs/uploads:
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

banana: htdocs/images/banana htdocs/css/banana.css include/banana/banana.inc.php
htdocs/images/banana:
	cd $(@D) && ln -snf $(BANANA)/img $(@F)

htdocs/css/banana.css:
	cd $(@D) && ln -snf $(BANANA)/css/style.css $(@F)

include/banana/banana.inc.php:
	cd $(@D) && find $(BANANA)/banana/ -name '*.php' -exec ln -snf {} . ";"



##
## jquery
##

jquery: htdocs/javascript/jquery.js htdocs/javascript/jquery.autocomplete.js htdocs/javascript/jquery.color.js
htdocs/javascript/jquery.js:
	wget http://jquery.com/src/jquery-latest.pack.js -O $@ -q || ($(RM) $@; exit 1)

htdocs/javascript/jquery.color.js:
	wget http://plugins.jquery.com/files/jquery.color.js.txt -O $@ -q || ($(RM) $@; exit 1)

################################################################################

.PHONY: build dist clean wiki build-wiki banana htdocs/images/banana htdocs/css/banana.css include/banana/banana.inc.php

