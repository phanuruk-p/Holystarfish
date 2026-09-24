(() => {
    const form = document.querySelector('#catalog-search');
    if (!form) return;
    const query = document.querySelector('#product-query');
    const grid = document.querySelector('.catalog-grid');
    const cards = [...grid.querySelectorAll('.product-card')];
    const buttons = [...document.querySelectorAll('[data-filter]')];
    let category = 'all';
    const groups = [
        ['kind', {'ต่างหู': ['ต่างหู', 'earrings', 'earring'], 'สร้อยคอ': ['สร้อยคอ', 'จี้', 'necklace', 'pendant'], 'แหวน': ['แหวน', 'ring'], 'กำไล': ['กำไล', 'สร้อยข้อมือ', 'bracelet'], 'เซ็ตของขวัญ': ['เซ็ตของขวัญ', 'ชุดของขวัญ', 'gift set']}],
        ['color', {'โรสโกลด์': ['โรสโกลด์', 'rose gold'], 'ทอง': ['สีทอง', 'โทนทอง', 'gold'], 'เงิน': ['สีเงิน', 'โทนเงิน', 'silver'], 'ขาว': ['สีขาว', 'โทนขาว'], 'ดำ': ['สีดำ', 'โทนดำ', 'black'], 'ฟ้า': ['สีฟ้า', 'โทนฟ้า', 'blue']}],
        ['style', {'มินิมอล': ['มินิมอล', 'เรียบง่าย', 'minimal'], 'คลาสสิก': ['คลาสสิก', 'classic'], 'หวาน': ['หวาน'], 'หรูหรา': ['หรูหรา', 'หรู'], 'แฟชั่น': ['แฟชั่น']}],
        ['occasion', {'ทุกวัน': ['ทุกวัน', 'ประจำวัน'], 'ทำงาน': ['ทำงาน', 'ออฟฟิศ'], 'งานเลี้ยง': ['งานเลี้ยง', 'ออกงาน', 'ปาร์ตี้'], 'เที่ยว': ['เที่ยว'], 'ของขวัญ': ['ของขวัญ', 'วันเกิด', 'ให้แฟน']}],
        ['tags', {'ไข่มุก': ['ไข่มุก', 'pearl'], 'คริสตัล': ['คริสตัล', 'crystal'], 'หัวใจ': ['หัวใจ'], 'ดาวทะเล': ['ดาวทะเล'], 'น้ำหนักเบา': ['น้ำหนักเบา']}],
    ];
    function parse(text) {
        let rest = text.toLowerCase().replace(/[๐-๙]/g, n => '๐๑๒๓๔๕๖๗๘๙'.indexOf(n)).replace(/(\d),(?=\d{3})/g, '$1');
        const constraints = [];
        let min = 0, max = Infinity;
        rest = rest.replace(/(?:ระหว่าง|ราคา|งบ)?\s*(\d+)\s*(?:-|–|ถึง)\s*(\d+)\s*(?:บาท)?/g, (_, a, b) => { min = Number(a); max = Number(b); return ' '; });
        rest = rest.replace(/(?:ไม่เกิน|สูงสุด|งบประมาณ|งบ|ไม่เกินราคา)\s*(\d+)\s*(?:บาท)?/g, (_, n) => { max = Math.min(max, Number(n)); return ' '; });
        rest = rest.replace(/(?:ตั้งแต่|อย่างน้อย|ขั้นต่ำ)\s*(\d+)\s*(?:บาท)?/g, (_, n) => { min = Math.max(min, Number(n)); return ' '; });
        for (const [field, values] of groups) {
            for (const [value, aliases] of Object.entries(values)) {
                let found = false;
                for (const alias of aliases) {
                    if (rest.includes(alias)) { found = true; rest = rest.split(alias).join(' '); }
                }
                if (found) constraints.push([field, value]);
            }
        }
        rest = rest.replace(/อยากได้|กำลังมองหา|ต้องการ|ประมาณ|เครื่องประดับ|สำหรับ|เหมาะกับ|ช่วยหา|ขอแบบ|แบบ|ใส่|โทน|ราคา|งบ|บาท|และ|ที่|สัก|ไม่เกิน/g, ' ');
        const words = rest.split(/[\s,，]+/).filter(Boolean);
        return {constraints, words, min, max};
    }
    function apply() {
        const parsed = parse(query.value);
        const min = Math.max(parsed.min, Number(form.elements.min.value || 0));
        const max = Math.min(parsed.max, form.elements.max.value === '' ? Infinity : Number(form.elements.max.value));
        const selected = ['kind', 'color', 'style', 'occasion'].flatMap(field => form.elements[field].value ? [[field, form.elements[field].value]] : []);
        const constraints = [...parsed.constraints, ...selected];
        const visible = cards.filter(card => {
            const d = card.dataset;
            const haystack = [d.name, d.detail, d.tags, d.kind, d.color, d.style, d.occasion, d.type].join(' ').toLowerCase();
            const matches = (category === 'all' || d.category === category) && Number(d.priceValue) >= min && Number(d.priceValue) <= max && constraints.every(([field, value]) => d[field].includes(value)) && parsed.words.every(word => haystack.includes(word));
            card.hidden = !matches;
            return matches;
        });
        const sort = form.elements.sort.value;
        visible.sort((a, b) => sort === 'low' ? a.dataset.priceValue - b.dataset.priceValue : sort === 'high' ? b.dataset.priceValue - a.dataset.priceValue : sort === 'name' ? a.dataset.name.localeCompare(b.dataset.name) : cards.indexOf(a) - cards.indexOf(b));
        visible.forEach(card => grid.append(card));
        document.querySelector('.catalog-count').textContent = `พบสินค้า ${visible.length} จาก ${cards.length} รายการ`;
        document.querySelector('.search-empty').hidden = visible.length !== 0;
        const labels = [...new Set(constraints.map(([, value]) => value))];
        if (min > 0) labels.push(`ตั้งแต่ ${min.toLocaleString('th-TH')} บาท`);
        if (max !== Infinity) labels.push(`ไม่เกิน ${max.toLocaleString('th-TH')} บาท`);
        if (parsed.words.length) labels.push(`คำค้น: ${parsed.words.join(' ')}`);
        document.querySelector('.search-summary').textContent = min > max ? 'ช่วงราคาไม่ถูกต้อง: ราคาต่ำสุดต้องไม่มากกว่าราคาสูงสุด' : labels.length ? `เงื่อนไขที่ใช้: ${labels.join(' · ')}` : 'เลือกดูทั้งหมด หรือบอกความต้องการเพื่อคัดกรองสินค้า';
        buttons.forEach(button => { const active = button.dataset.filter === category; button.classList.toggle('active', active); button.setAttribute('aria-pressed', String(active)); });
    }
    form.addEventListener('submit', event => { event.preventDefault(); apply(); });
    form.addEventListener('input', apply);
    form.addEventListener('change', apply);
    form.addEventListener('reset', () => { category = 'all'; setTimeout(apply, 0); });
    buttons.forEach(button => button.addEventListener('click', () => { category = button.dataset.filter; apply(); }));
    document.querySelectorAll('[data-query]').forEach(button => button.addEventListener('click', () => {
        form.reset(); category = 'all'; query.value = button.dataset.query; apply();
    }));
    document.querySelector('#clear-empty').addEventListener('click', () => { form.reset(); query.focus(); });
    apply();
})();
