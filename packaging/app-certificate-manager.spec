
Name: app-certificate-manager
Epoch: 1
Version: 2.4.22
Release: 1%{dist}
Summary: Certificate Manager
License: GPLv3
Group: Applications/Apps
Packager: ClearFoundation
Vendor: ClearFoundation
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-accounts

%description
Security certificates are an industry standard for encrypting information sent over a network, providing authentication and enabling digital signatures.  The Certificate Manager app provides the ability to create, manage and deploy security certificates to users and apps.

%package core
Summary: Certificate Manager - API
License: LGPLv3
Group: Applications/API
Requires: app-base-core
Requires: /usr/bin/getent
Requires: /usr/sbin/groupadd
Requires: app-base-core >= 1:2.3.39
Requires: app-events-core >= 1:1.6.0
Requires: app-network-core
Requires: app-organization-core
Requires: app-mode-core
Requires: clearos-framework >= 7.3.8
Requires: csplugin-filesync
Requires: openssl >= 1.0.0

%description core
Security certificates are an industry standard for encrypting information sent over a network, providing authentication and enabling digital signatures.  The Certificate Manager app provides the ability to create, manage and deploy security certificates to users and apps.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/certificate_manager
cp -r * %{buildroot}/usr/clearos/apps/certificate_manager/

install -d -m 0755 %{buildroot}/etc/clearos/certificate_manager.d
install -d -m 0755 %{buildroot}/var/clearos/certificate_manager
install -d -m 0755 %{buildroot}/var/clearos/certificate_manager/backup
install -d -m 0755 %{buildroot}/var/clearos/certificate_manager/state
install -d -m 0755 %{buildroot}/var/clearos/events/certificate_manager
install -D -m 0755 packaging/certificate_manager_event %{buildroot}/var/clearos/events/certificate_manager/certificate_manager
install -D -m 0644 packaging/filewatch-certificate-manager-event.conf %{buildroot}/etc/clearsync.d/filewatch-certificate-manager-event.conf
install -D -m 0644 packaging/index.txt %{buildroot}/etc/pki/CA/index.txt
install -D -m 0644 packaging/serial %{buildroot}/etc/pki/CA/serial

%pre core
/usr/bin/getent group ssl-cert >/dev/null || /usr/sbin/groupadd -r ssl-cert

%post
logger -p local6.notice -t installer 'app-certificate-manager - installing'

%post core
logger -p local6.notice -t installer 'app-certificate-manager-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/certificate_manager/deploy/install ] && /usr/clearos/apps/certificate_manager/deploy/install
fi

[ -x /usr/clearos/apps/certificate_manager/deploy/upgrade ] && /usr/clearos/apps/certificate_manager/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-certificate-manager - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-certificate-manager-core - uninstalling'
    [ -x /usr/clearos/apps/certificate_manager/deploy/uninstall ] && /usr/clearos/apps/certificate_manager/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/certificate_manager/controllers
/usr/clearos/apps/certificate_manager/htdocs
/usr/clearos/apps/certificate_manager/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/certificate_manager/packaging
%exclude /usr/clearos/apps/certificate_manager/unify.json
%dir /usr/clearos/apps/certificate_manager
%dir %attr(0755,root,ssl-cert) /etc/clearos/certificate_manager.d
%dir /var/clearos/certificate_manager
%dir /var/clearos/certificate_manager/backup
%dir /var/clearos/certificate_manager/state
%dir /var/clearos/events/certificate_manager
/usr/clearos/apps/certificate_manager/deploy
/usr/clearos/apps/certificate_manager/language
/usr/clearos/apps/certificate_manager/libraries
/var/clearos/events/certificate_manager/certificate_manager
/etc/clearsync.d/filewatch-certificate-manager-event.conf
%config(noreplace) /etc/pki/CA/index.txt
%config(noreplace) /etc/pki/CA/serial
