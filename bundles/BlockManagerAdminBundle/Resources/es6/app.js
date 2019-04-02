import './helpers/closestPolyfill';
import NetgenCore from '@netgen/layouts-ui-core';
import NlLayouts from './components/layouts';
import NlRules from './components/rules';
// import './components/plugins';
import initPlugins from './components/plugins';

const { $ } = NetgenCore;

NetgenCore.ngLayoutsInit = () => {
    const layoutsEl = document.getElementById('layouts');
    const rulesEl = document.getElementById('rules');
    NetgenCore.nlLayouts = layoutsEl ? new NlLayouts(layoutsEl) : null;
    NetgenCore.nlRules = rulesEl ? new NlRules(rulesEl) : null;

    initPlugins();

    $(document).on('click', '.js-open-bm', () => {
        localStorage.setItem('bm_referrer', window.location.href);
    });
};

$(document).ready(() => {
    NetgenCore.ngLayoutsInit();
});
