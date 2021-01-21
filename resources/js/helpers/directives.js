// Vue.directive('can', {
//     terminal: true,
//     bind: function(el, bindings) {
//         const canI = Vue.prototype.$can(bindings.value, bindings.modifiers.one);

//         if(!canI) {
//             this.warning = document.createElement('p');
//             this.warning.className = 'alert alert-warning v-can-warning';
//             this.warning.innerHTML = 'You do not have permission to access this page';
//             for(let i=0; i<el.children.length; i++) {
//                 let c = el.children[i];
//                 c.classList.add('v-can-hidden');
//             }
//             el.appendChild(this.warning);
//         }
//     },
//     unbind: function(el) {
//         if(!el.children) return;
//         for(let i=0; i<el.children.length; i++) {
//             let c = el.children[i];
//             // remove our warning elem
//             if(c.classList.contains('v-can-warning')) {
//                 el.removeChild(c);
//                 continue;
//             }
//             if(c.classList.contains('v-can-hidden')) {
//                 c.classList.remove('v-can-hidden');
//             }
//         }
//     }
// });