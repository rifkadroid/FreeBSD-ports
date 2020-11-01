PHP_ARG_ENABLE(Kontrol, whether to enable Kontrol support,
[ --enable-Kontrol   Enable Kontrol support])

PHP_SUBST(KONTROL_SHARED_LIBADD)
PHP_ADD_LIBRARY_WITH_PATH(netgraph, /usr/lib, KONTROL_SHARED_LIBADD)
PHP_ADD_LIBRARY_WITH_PATH(vici, /usr/local/lib/ipsec, KONTROL_SHARED_LIBADD)
if test "$PHP_KONTROL" = "yes"; then
  AC_DEFINE(HAVE_PFSENSE, 1, [Whether you have Kontrol])
  PHP_NEW_EXTENSION(Kontrol, Kontrol.c %%DUMMYNET%% %%ETHERSWITCH%%, $ext_shared)
fi
