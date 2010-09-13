
# $Id: Makefile,v 1.5 2004/11/25 20:18:39 x99laine Exp $
################################################################################
# definitions

VERSNUM := $(shell grep VERSION ChangeLog | head -1 | sed -e "s/VERSION //;s/ .*//")
VERSTAG := $(shell grep VERSION ChangeLog | head -1 | grep 'XX' > /dev/null 2> /dev/null && echo 'beta')

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

build: core conf static banana wiki openid medals jquery

check:
	@!(find . -name '*.php' -exec php -l {} ";" | grep -v 'No syntax errors detected')

test:
	make -C core test

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

core:
	[ -d core/.git ] || ( git submodule init && git submodule update )
	make -C core all

##
## conf
##

conf: spool/templates_c spool/mails_c classes/platalglobals.php configs/platal.cron htdocs/.htaccess spool/conf spool/tmp

spool/templates_c spool/mails_c spool/uploads spool/conf spool/tmp spool/run:
	mkdir -p $@
	chmod o+w $@

htdocs/.htaccess: htdocs/.htaccess.in Makefile
	@REWRITE_BASE="/~$$(id -un)"; \
	test "$$REWRITE_BASE" = "/~web" && REWRITE_BASE="/"; \
	sed -e "s,@REWRITE_BASE@,$$REWRITE_BASE,g" $< > $@

##
## static content
##
static: htdocs/javascript@VERSION

%@VERSION: % Makefile ChangeLog
	cd $< && rm -f $(VERSION) && ln -sf . $(VERSION)

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
	    tar -xzvf pmwiki-latest.tgz;                                  \
	    rm pmwiki-latest.tgz;                                         \
	    mv pmwiki-* wiki;                                             \
	fi

##
## openid
##

openid: get-openid spool/openid/store

# There is no obvious way to automatically use the latest version
OPENID_VERSION = 2.1.3
get-openid:
	@if ! test -d include/Auth; then                                  \
	    wget http://openidenabled.com/files/php-openid/packages/php-openid-$(OPENID_VERSION).tar.bz2; \
	    tar -xjf php-openid-$(OPENID_VERSION).tar.bz2;                \
	    mv php-openid-$(OPENID_VERSION)/Auth include/;                \
	    rm php-openid-$(OPENID_VERSION).tar.bz2;                      \
	    rm -r php-openid-$(OPENID_VERSION);                           \
	fi

spool/openid/store:
	mkdir -p $@
	chmod o+w $@

##
## banana
##
banana: htdocs/images/banana htdocs/css/banana.css
htdocs/images/banana: banana-sub
	cd $(@D) && ln -snf ../../banana/img $(@F)

htdocs/css/banana.css: banana-sub
	cd $(@D) && ln -snf ../../banana/css/style.css $(@F)

banana-sub:
	make -C banana

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
JQUERY_VERSION=1.4.2
JQUERY_PLUGINS=color form
JQUERY_PLUGINS_PATHES=$(addprefix htdocs/javascript/jquery.,$(addsuffix .js,$(JQUERY_PLUGINS)))

JQUERY_UI_VERSION=1.6
JQUERY_UI=core tabs
JQUERY_UI_PATHES=$(addprefix htdocs/javascript/jquery.ui.,$(addsuffix .js,$(JQUERY_UI)))

JSTREE_VERSION=1.0rc2
JSTREE_PATH=htdocs/javascript/jquery.jstree.js

# TODO: jquery.autocomplete.js should rather be downloaded from an official source. The issue
# is that the version we use is not available anymore on the Internet, and the latest version
# we could use is not backward compatible with our current code.
jquery: htdocs/javascript/jquery.js $(JQUERY_PLUGINS_PATHES) $(JQUERY_UI_PATHES) $(JSTREE_PATH)

htdocs/javascript/jquery-$(JQUERY_VERSION).min.js: DOWNLOAD_SRC = http://jquery.com/src/$(@F)
htdocs/javascript/jquery-$(JQUERY_VERSION).min.js:
	@-rm htdocs/javascript/jquery-*.min.js
	@$(download)

htdocs/javascript/jquery.js: htdocs/javascript/jquery-$(JQUERY_VERSION).min.js
	ln -snf $(<F) $@

$(JQUERY_PLUGINS_PATHES): DOWNLOAD_SRC = http://plugins.jquery.com/files/$(@F)_0.txt
$(JQUERY_PLUGINS_PATHES):
	@-rm htdocs/javascript/jquery.ui*.$*.js
	@$(download)

htdocs/javascript/jquery.ui-$(JQUERY_UI_VERSION).%.js: DOWNLOAD_SRC = http://jquery-ui.googlecode.com/svn/tags/$(JQUERY_UI_VERSION)/ui/ui.$*.js
htdocs/javascript/jquery.ui-$(JQUERY_UI_VERSION).%.js:
	@$(download)

$(JQUERY_UI_PATHES): htdocs/javascript/jquery.ui.%.js: htdocs/javascript/jquery.ui-$(JQUERY_UI_VERSION).%.js
	ln -snf $(<F) $@

$(JSTREE_PATH):
	rm -f htdocs/javascript/jquery.jstree-*.js
	mkdir spool/tmp/jstree
	wget http://jstree.googlecode.com/files/jsTree.v.$(JSTREE_VERSION).zip -O spool/tmp/jstree/jquery.jstree-$(JSTREE_VERSION).zip
	unzip spool/tmp/jstree/jquery.jstree-$(JSTREE_VERSION).zip -d spool/tmp/jstree/
	mv -f spool/tmp/jstree/themes/default/style.css htdocs/css/jstree.css
	mv -f spool/tmp/jstree/themes/default/d.png htdocs/images/jstree.png
	mv -f spool/tmp/jstree/jquery.jstree.js htdocs/javascript/jquery.jstree-$(JSTREE_VERSION).js
	ln -snf jquery.jstree-$(JSTREE_VERSION).js htdocs/javascript/jquery.jstree.js
	rm -Rf spool/tmp/jstree


##
## lists rpc
##
start-listrpc: spool/run
	sudo -u list /sbin/start-stop-daemon --pidfile spool/run/listrpc.pid -m -b -x $$PWD/bin/lists.rpc.py --start
	@sleep 2
	@sudo -u list kill -0 $$(cat spool/run/listrpc.pid)

start-listrpc-fg: spool/run
	sudo -u list ./bin/lists.rpc.py

stop-listrpc:
	-sudo -u list /sbin/start-stop-daemon --pidfile spool/run/listrpc.pid --stop
	@-rm -f spool/run/listrpc.pid

restart-listrpc: stop-listrpc start-listrpc

################################################################################

.PHONY: build dist clean core http* check test
.PHONY: wiki build-wiki
.PHONY: banana banana-sub htdocs/images/banana htdocs/css/banana.css
.PHONY: start-listrpc start-listrpc-fg stop-listrpc restart-listrpc
