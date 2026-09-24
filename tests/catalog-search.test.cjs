const {readFileSync} = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const element = () => ({value: '', hidden: false, dataset: {}, handlers: {}, addEventListener(event, fn) {this.handlers[event] = fn;}, classList: {toggle() {}}, setAttribute() {}, focus() {}});
const form = element();
form.elements = Object.fromEntries(['min', 'max', 'sort', 'kind', 'color', 'style', 'occasion'].map(key => [key, element()]));
const query = element();
const cards = [
    {name: 'Pearl Tide Earrings', kind: 'ต่างหู', color: 'ขาว', style: 'หรูหรา', occasion: 'ทำงาน งานเลี้ยง ของขวัญ', tags: 'ไข่มุก คริสตัล', priceValue: '1290', category: 'signature'},
    {name: 'Silver Wave Ring', kind: 'แหวน', color: 'เงิน', style: 'มินิมอล', occasion: 'ทุกวัน ทำงาน', tags: 'เรียบง่าย', priceValue: '590', category: 'signature'},
    {name: 'Little Love Gift Set', kind: 'เซ็ตของขวัญ', color: 'โรสโกลด์', style: 'หวาน', occasion: 'ของขวัญ', tags: 'กล่อง', priceValue: '1490', category: 'gift'},
].map(dataset => ({...element(), dataset}));
const order = [];
const grid = {querySelectorAll: () => cards, append(card) {order.push(card.dataset.name);}};
const selectors = {'#catalog-search': form, '#product-query': query, '.catalog-grid': grid};
for (const key of ['.catalog-count', '.search-empty', '.search-summary', '#clear-empty']) selectors[key] = element();
const buttons = ['all', 'signature', 'brandname', 'gift'].map(filter => ({...element(), dataset: {filter}}));
const timers = [];
form.reset = () => {query.value = ''; Object.values(form.elements).forEach(e => e.value = ''); form.handlers.reset(); timers.splice(0).forEach(fn => fn());};
vm.runInNewContext(readFileSync('assets/catalog-search.js', 'utf8'), {
    document: {querySelector: key => selectors[key], querySelectorAll: key => key === '[data-filter]' ? buttons : []},
    setTimeout: fn => timers.push(fn),
});
function search(text, expected) {query.value = text; form.handlers.input(); assert.deepEqual(cards.filter(c => !c.hidden).map(c => c.dataset.name), expected);}
search('อยากได้ต่างหูไข่มุก ใส่ทำงาน งบไม่เกิน 1,500', ['Pearl Tide Earrings']);
search('มินิมอล ไม่เกิน ๑๐๐๐', ['Silver Wave Ring']);
search('ของขวัญ โทนโรสโกลด์', ['Little Love Gift Set']);
search('ราคา 1000-1400', ['Pearl Tide Earrings']);
search('ไม่มีสินค้าชื่อนี้', []);
assert.equal(selectors['.search-empty'].hidden, false);
search('', cards.map(c => c.dataset.name));
form.elements.min.value = '2000'; form.elements.max.value = '1000'; form.handlers.change();
assert.match(selectors['.search-summary'].textContent, /ช่วงราคาไม่ถูกต้อง/);
form.reset(); form.elements.color.value = 'ขาว'; search('ต่างหู', ['Pearl Tide Earrings']);
buttons[3].handlers.click(); assert.equal(cards.every(c => c.hidden), true);
form.reset(); assert.equal(cards.every(c => !c.hidden), true);
form.elements.sort.value = 'low'; order.length = 0; form.handlers.change();
assert.deepEqual(order, ['Silver Wave Ring', 'Pearl Tide Earrings', 'Little Love Gift Set']);
console.log('Catalog search checks passed: Thai queries, budgets, filters, empty state, reset, and sorting.');
