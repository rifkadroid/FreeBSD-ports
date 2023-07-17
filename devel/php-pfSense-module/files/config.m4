PHP_ARG_ENABLE([kontrol],
  [whether to enable kontrol support],
  [AS_HELP_STRING([--enable-kontrol],
    [Enable kontrol support])],
  [no])

PHP_ADD_INCLUDE(/usr/local/include)

PHP_ADD_LIBRARY_WITH_PATH(netgraph, /usr/lib, KONTROL_SHARED_LIBADD)
PHP_ADD_LIBRARY_WITH_PATH(pfctl, /usr/lib, KONTROL_SHARED_LIBADD)
PHP_ADD_LIBRARY_WITH_PATH(vici, /usr/local/lib/ipsec, KONTROL_SHARED_LIBADD)

PHP_SUBST(KONTROL_SHARED_LIBADD)

if test "$PHP_KONTROL" != "no"; then
  AC_DEFINE(HAVE_KONTROL, 1, [ Have kontrol support ])
  PHP_NEW_EXTENSION(Kontrol, Kontrol.c %%DUMMYNET%% %%ETHERSWITCH%%, $ext_shared)
fi
