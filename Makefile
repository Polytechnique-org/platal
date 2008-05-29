
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

define download
@echo "Downloading $@ from $(DOWNLOAD_SRC)"
wget $(DOWNLOAD_SRC) -O $@ -q || ($(RM) $@; exit 1)
endef

################################################################################
# global targets

all: build

build: core banana wiki medals jquery

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
## Medal thumbnails
##
MEDAL_PICTURES=$(wildcard htdocs/images/medals/*.jpg)
MEDAL_THUMBNAILS=$(subst /medals/,/medals/thumb/,$(MEDAL_PICTURES))

medals: $(MEDAL_THUMBNAILS)

$(MEDAL_THUMBNAILS): $(subst /medals/thumb/,/medals/,$(@F))
	convert -resize x50 $(subst /medals/thumb/,/medals/,$@) $@

##
## jquery
##

JQUERY_PLUGINS=color
JQUERY_PLUGINS_PATHES=$(addprefix htdocs/javascript/jquery.,$(addsuffix .js,$(JQUERY_PLUGINS)))

jquery: htdocs/javascript/jquery.js htdocs/javascript/jquery.autocomplete.js $(JQUERY_PLUGINS_PATHES)

htdocs/javascript/jquery.js: DOWNLOAD_SRC = http://jquery.com/src/jquery-latest.pack.js
htdocs/javascript/jquery.js:
	@$(download)

htdocs/javascript/jquery.autocomplete.js: DOWNLOAD_SRC = http://jquery-autocomplete.googlecode.com/svn/trunk/jquery.autocomplete.js
htdocs/javascript/jquery.autocomplete.js:
	@$(download)

$(JQUERY_PLUGINS_PATHES): DOWNLOAD_SRC = http://plugins.jquery.com/files/$(@F).txt
$(JQUERY_PLUGINS_PATHES):
	@$(download)

################################################################################

.PHONY: build dist clean wiki build-wiki banana htdocs/images/banana htdocs/css/banana.css include/banana/banana.inc.php http*

