(() => { window.setLocale=(locale)=>{ if(!['fr','en'].includes(locale)) return; window.location.assign('/locale/'+locale); }; })();
