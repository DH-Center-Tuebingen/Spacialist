import hljs from 'highlight.js';
import i18n from '@/bootstrap/i18n.js';
import { can, _debounce, getElementAttribute } from '@/helpers/helpers.js';

export default function initDirectives(app) {
    // Directives
    app.directive('dcan', {
        terminal: true,
        beforeMount(el, bindings) {
            const canI = can(bindings.value, bindings.modifiers.one);

            if(!canI) {
                const warningElem = document.createElement('p');
                warningElem.className = 'alert alert-warning v-can-warning';
                warningElem.innerHTML = i18n.global.t('main.app.page_access_denied', { perm: bindings.value });
                for(let i = 0; i < el.children.length; i++) {
                    let c = el.children[i];
                    c.classList.add('v-can-hidden');
                }
                el.appendChild(warningElem);
            }
        },
        unmounted(el) {
            if(!el.children) return;
            for(let i = 0; i < el.children.length; i++) {
                let c = el.children[i];
                // remove our warning elem
                if(c.classList.contains('v-can-warning')) {
                    el.removeChild(c);
                    continue;
                }
                if(c.classList.contains('v-can-hidden')) {
                    c.classList.remove('v-can-hidden');
                }
            }
        }
    });
    app.directive('highlightjs', {
        deep: true,
        beforeMount(el, binding) {
            if(!binding.value) return;
            // on first bind, highlight all targets
            let targets = el.querySelectorAll('code');
            targets.forEach((target) => {
                // if a value is directly assigned to the directive, use this
                // instead of the element content.
                if(binding.value) {
                    target.innerHTML = binding.value;
                }
                hljs.highlightElement(target);
            });
        },
        updated(el, binding) {
            if(!binding.value) return;
            // after an update, re-fill the content and then highlight
            let targets = el.querySelectorAll('code');
            targets.forEach((target) => {
                if(binding.value) {
                    target.innerHTML = binding.value;
                    hljs.highlightElement(target);
                }
            });
        }
    });
    app.directive('resize', {
        beforeMount(el, binding) {
            if(!binding.value) return;

            const resizeCallback = binding.value;
            window.addEventListener('resize', () => {
                const height = document.documentElement.clientHeight;
                const width = document.documentElement.clientWidth;
                resizeCallback({
                    height: height,
                    width: width,
                });
            });
        },
        updated(el, binding) {
            if(!binding.value) return;
            // after an update, re-fill the content and then highlight
            let targets = el.querySelectorAll('code');
            targets.forEach((target) => {
                if(binding.value) {
                    target.innerHTML = binding.value;
                    hljs.highlightElement(target);
                }
            });
        }
    });
    app.directive('infinite-scroll', {
        mounted(el, binding) {
            const options = {
                disabled: false,
                delay: 200,
                offset: 0,
            };

            const disabled = !!getElementAttribute(el, 'infinite-scroll-disabled', options.disabled, 'bool');
            const delay = getElementAttribute(el, 'infinite-scroll-delay', options.delay, 'int');
            const offset = getElementAttribute(el, 'infinite-scroll-offset', options.offset, 'int');

            if(!disabled) {
                el.onscroll = _debounce(_ => {
                    const position = el.clientHeight + el.scrollTop;
                    const threshold = el.scrollHeight - offset;

                    if(position >= threshold) {
                        binding.value();
                    }
                }, delay);
            }
        },
        updated(el, binding) {
            const options = {
                disabled: false,
                delay: 200,
                offset: 0,
            };

            const disabled = !!getElementAttribute(el, 'infinite-scroll-disabled', options.disabled, 'bool');

            if(disabled) {
                el.onscroll = null;
            } else if(!disabled && !el.onscroll) {
                const delay = getElementAttribute(el, 'infinite-scroll-delay', options.delay, 'int');
                const offset = getElementAttribute(el, 'infinite-scroll-offset', options.offset, 'int');

                el.onscroll = _debounce(_ => {
                    const position = el.clientHeight + el.scrollTop;
                    const threshold = el.scrollHeight - offset;

                    if(position >= threshold) {
                        binding.value();
                    }
                }, delay);
            }
        },
        beforeUnmount(el, binding) {
            el.onscroll = null;
        }
    });


    function updateVisibility (el, binding) {
        el.style.opacity = binding.value ? '1.0' : '0.0';
    }
    app.directive('visible', {
        mounted(el, binding, vnode) {
            const transition = 'opacity 0.3s';
            if(!el.style.transition) el.style.transition = transition;
            updateVisibility(el, binding);
        },
        updated(el, binding, vnode, prevVnode) {
            updateVisibility(el, binding);
        },
    });
}