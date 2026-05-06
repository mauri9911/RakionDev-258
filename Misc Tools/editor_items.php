<?php
// ==============================================================================
// BACKEND PHP: Sincronización DB
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data && isset($data['action']) && $data['action'] === 'sync_db') {
        header('Content-Type: application/json');

        $host = "127.0.0.1"; $user = "root"; $pass = "1234567"; $db = "rakion";
        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) { echo json_encode(['success' => false, 'message' => 'Error MySQL: ' . $conn->connect_error]); exit; }
        $conn->set_charset("utf8");
        $conn->query("TRUNCATE TABLE iteminfo");

        $sql = "INSERT INTO iteminfo (id, name, type, Class, level, shop, gold, cash, hit1, hit2, hit3, hit4, chit, ap, hp, maxcp, power, textureview) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $conn->error]); exit; }

        $count = 0;
        foreach ($data['items'] as $item) {
            $stmt->bind_param("isiiiiiiiiiiiiiiii", $item['itemId'], $item['name'], $item['itemType'], $item['cls'], $item['level'], $item['shop'], $item['gold'], $item['cash'], $item['basic'], $item['atqL'], $item['atqE'], $item['atqLl'], $item['destCell'], $item['armor'], $item['energia'], $item['ptsCell'], $item['power'], $item['textureView']);
            if ($stmt->execute()) $count++;
        }
        $stmt->close(); $conn->close();
        echo json_encode(['success' => true, 'message' => "Sincronización completa. $count ítems insertados."]); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rakion Neon - Items.DAT & XFS Studio Pro</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pako/2.1.0/pako.min.js"></script>
    <style>
        :root { --bg: #0d0d12; --bg-sec: #16161e; --primary: #00d4ff; --text: #e0e0e0; --danger: #ff0055; --success: #00ff88; --warning: #ffcc00; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        
        .toolbar { background: var(--bg-sec); padding: 15px 20px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; border-bottom: 1px solid #333; z-index: 10; }
        .toolbar button { color: #000; border: none; padding: 8px 16px; font-weight: bold; cursor: pointer; border-radius: 4px; transition: 0.2s; display: flex; align-items: center; gap: 6px; text-transform: uppercase; font-size: 11px; }
        .btn-blue { background: var(--primary); }
        .btn-green { background: var(--success); }
        .btn-purple { background: #bc13fe; color: #fff !important; }
        .btn-yellow { background: var(--warning); }
        .btn-red { background: var(--danger); color: #fff !important; }
        .btn-sql { background: #ff7700; color: #000 !important; }
        .btn-xfs { background: #6c757d; color: #fff !important; border: 1px solid #aaa !important; }
        .toolbar button:hover { filter: brightness(1.2); transform: translateY(-1px); }
        .toolbar input[type="file"] { display: none; }
        
        .nav-bar { background: #111118; padding: 10px 20px; display: flex; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid #222; align-items: center; }
        .nav-bar input, .nav-bar select { background: #0a0a0f; border: 1px solid #333; color: white; padding: 6px 10px; font-family: Consolas, monospace; border-radius: 4px; font-size: 13px;}
        .nav-bar input:focus, .nav-bar select:focus { border-color: var(--primary); outline: none; }
        .nav-bar input[type="text"] { flex-grow: 1; min-width: 200px; }
        .nav-bar button { background: #222; color: var(--primary); border: 1px solid #444; padding: 6px 15px; font-weight: bold; cursor: pointer; border-radius: 4px; }
        
        .main-container { display: flex; flex-grow: 1; overflow: hidden; }
        .sidebar { width: 340px; background: var(--bg-sec); border-right: 1px solid #222; display: flex; flex-direction: column; z-index: 5; }
        .sidebar-list { flex-grow: 1; overflow-y: auto; list-style: none; margin: 0; padding: 0; }
        .sidebar-list li { padding: 12px 15px; border-bottom: 1px solid #222; cursor: pointer; font-size: 13px; font-family: Consolas, monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #aaa; }
        .sidebar-list li:hover { background: #2a2a35; color: #fff; }
        .sidebar-list li.active { background: rgba(0, 212, 255, 0.1); border-left: 4px solid var(--primary); color: #fff; font-weight: bold; }
        .id-badge { color: var(--warning); display: inline-block; width: 45px; }
        
        .editor-area { flex-grow: 1; padding: 30px; overflow-y: auto; background: var(--bg); position: relative; }
        .editor-title { margin-top: 0; color: #fff; font-weight: 400; font-size: 22px; }
        .editor-title span { color: var(--primary); font-weight: bold; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 15px; }
        .form-group { display: flex; flex-direction: column; background: rgba(255,255,255,0.02); padding: 10px; border-radius: 6px; border: 1px solid #222; position: relative; }
        .form-group label { color: var(--primary); font-family: Consolas, monospace; font-size: 11px; font-weight: bold; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;}
        .form-group input { background: #0a0a0f; border: 1px solid #333; color: white; padding: 8px; font-family: Consolas, monospace; border-radius: 4px; width: 100%; box-sizing: border-box; transition: 0.3s; }
        .form-group input.valid-path { color: var(--success); font-weight: bold; }
        .form-group input.invalid-path { color: var(--danger); font-weight: bold; border-color: var(--danger); }
        .form-group input[type="color"] { padding: 0; height: 35px; cursor: pointer; border: none; }
        .full-width { grid-column: 1 / -1; }

        .btn-edit-code { position: absolute; right: 10px; top: 8px; background: var(--primary); border: none; color: #000; font-size: 10px; font-weight: bold; padding: 2px 8px; border-radius: 3px; cursor: pointer; }
        .btn-edit-code:hover { background: #fff; }

        .preview-box { width: 80px; height: 80px; background: rgba(0,0,0,0.5); border: 1px solid var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 0 15px rgba(0, 212, 255, 0.2); }
        .preview-box img { max-width: 90%; max-height: 90%; filter: drop-shadow(0 0 5px var(--primary)); }

        #codeEditorModal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 100; justify-content: center; align-items: center; }
        .modal-content { background: #16161e; border: 1px solid var(--primary); width: 85%; height: 85%; display: flex; flex-direction: column; border-radius: 8px; overflow: hidden; box-shadow: 0 0 30px rgba(0, 212, 255, 0.3); }
        .modal-header { padding: 15px; background: #111; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { margin: 0; color: var(--primary); font-family: Consolas; font-size: 16px; }
        .modal-body { flex-grow: 1; display: flex; }
        .modal-body textarea { width: 100%; height: 100%; background: #0a0a0f; color: #00ff88; font-family: Consolas, monospace; font-size: 14px; border: none; padding: 15px; resize: none; outline: none; }
        .modal-footer { padding: 15px; background: #111; border-top: 1px solid #333; display: flex; justify-content: flex-end; gap: 10px; }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
    </style>
</head>
<body>

    <datalist id="xfs-datalist"></datalist>

    <div class="toolbar">
        <button class="btn-blue" onclick="document.getElementById('fileInput').click()">📁 ABRIR ITEMS.DAT</button>
        <button class="btn-xfs" onclick="document.getElementById('xfsInput').click()">📦 CARGAR .XFS</button>
        
        <button class="btn-green" onclick="saveChanges()" id="btnSave" style="display:none;">💾 DESCARGAR .DAT</button>
        <button class="btn-sql" onclick="syncDB()" id="btnSql" style="display:none;">⚡ SINCRONIZAR DB</button>
        
        <div style="border-left: 1px solid #444; height: 25px; margin: 0 10px;" id="sep1" style="display:none;"></div>
        
        <button class="btn-purple" onclick="addNewItem()" id="btnAdd" style="display:none;">➕ NUEVO</button>
        <button class="btn-yellow" onclick="duplicateItem()" id="btnDup" style="display:none;">📋 DUPLICAR</button>
        <button class="btn-red" onclick="deleteItem()" id="btnDel" style="display:none;">🗑️ ELIMINAR</button>
        
        <div style="margin-left: auto; display: flex; align-items: center; gap: 10px;">
            <span id="xfsStatus" style="font-size: 11px; color: var(--warning); font-family: Consolas; margin-right: 5px;">XFS No Cargado</span>
            <label style="font-size: 11px; color: var(--primary); font-weight: bold;">URL ICONOS:</label>
            <input type="text" id="iconBasePath" placeholder="/rakion/paneluser/modules/img/items/" value="/rakion/paneluser/modules/img/items/" style="background:#0a0a0f; color:#fff; border:1px solid #444; padding:5px; border-radius:3px; font-family:Consolas; width: 180px;">
        </div>
        
        <input type="file" id="fileInput" accept=".dat">
        <input type="file" id="xfsInput" accept=".xfs" multiple>
    </div>

    <div class="nav-bar" id="filtersBar" style="display:none;">
        <button onclick="navigate(-1)">&lt;</button>
        <input type="text" id="filterName" placeholder="🔍 Buscar por nombre..." oninput="applyFilters()">
        <select id="filterClass" onchange="applyFilters()">
            <option value="">Clase (Todas)</option>
            <option value="1">Swordman</option>
            <option value="2">Archer</option>
            <option value="4">Blacksmith</option>
            <option value="8">Mage</option>
            <option value="16">Ninja</option>
            <option value="31">Universal</option>
        </select>
        <select id="filterType" onchange="applyFilters()">
            <option value="">Tipo (Todos)</option>
            <option value="0">Cabeza</option>
            <option value="1">Cuerpo</option>
            <option value="2">Hombreras</option>
            <option value="3">Brazos</option>
            <option value="4">Arma Primaria</option>
            <option value="5">Arma Secundaria</option>
            <option value="6">Collares</option>
            <option value="7">Anillos</option>
            <option value="8">Criaturas</option>
            <option value="9">Tickets</option>
            <option value="10">Set</option>
            <option value="12">Pociones</option>
        </select>
        <input type="number" id="filterLevel" placeholder="Nivel Mín." oninput="applyFilters()" style="width: 90px;">
        <button onclick="navigate(1)">&gt;</button>
        <span id="statusText" style="margin-left:auto; font-size: 13px; color: #888; font-family: Consolas;"></span>
    </div>

    <div class="main-container">
        <div class="sidebar">
            <ul class="sidebar-list" id="itemList"></ul>
        </div>
        <div class="editor-area" id="editorArea" style="display:none;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
                <h2 class="editor-title" id="itemHeaderTitle" style="border:none; margin:0; padding:0;"></h2>
                <div class="preview-box">
                    <img id="itemPreviewImg" src="" alt="Icono" onerror="this.style.display='none'" onload="this.style.display='block'">
                </div>
            </div>
            <div class="form-grid" id="formGrid"></div>
        </div>
    </div>

    <div id="codeEditorModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="codeEditorTitle">Visualizador de Archivo</h3>
                <button onclick="closeCodeEditor()" style="background:transparent; border:none; color:white; font-size:16px; cursor:pointer;">✖</button>
            </div>
            <div class="modal-body">
                <textarea id="codeEditorTextarea" spellcheck="false"></textarea>
            </div>
            <div class="modal-footer">
                <span style="margin-right:auto; color:#888; font-size:12px;">Nota: Solo lectura desde RAM. Edita y descarga para guardar.</span>
                <button onclick="closeCodeEditor()" style="background:#444; border:none; color:white; padding:8px 15px; border-radius:3px; cursor:pointer;">Cerrar</button>
                <button onclick="downloadEditedCode()" id="btnSaveCode" style="background:var(--success); border:none; color:black; padding:8px 15px; font-weight:bold; border-radius:3px; cursor:pointer;">💾 Descargar Archivo</button>
            </div>
        </div>
    </div>

    <script>
        let fileData = null; 
        let parsedItems = []; 
        let currentIdx = -1;
        let xfsRegistry = new Map(); 
        let xfsBuffers = [];         

        const FIELDS = [
            { id: "itemId", label: "Item ID", type: "uint16" },
            { id: "position", label: "Posición", type: "uint16" },
            { id: "textureView", label: "Texture View (ID de Imagen)", type: "uint16" },
            { id: "name", label: "Nombre", type: "string", fw: true },
            { id: "transparency", label: "Transparencia (Alpha)", type: "uint8" },
            { id: "colorHex", label: "Color (RGB)", type: "color" },
            { id: "pathModel", label: "Ruta del Modelo (.smc)", type: "file", fw: true },
            { id: "cls", label: "Clase", type: "uint8" },
            { id: "level", label: "Nivel Req.", type: "uint8" },
            { id: "gold", label: "Precio Gold", type: "uint32" },
            { id: "cash", label: "Precio Cash", type: "uint32" },
            { id: "shop", label: "En Tienda", type: "uint8" },
            { id: "power", label: "Power User", type: "uint8" },
            { id: "itemType", label: "Tipo", type: "uint8" },
            { id: "unknow", label: "Unknow", type: "uint8" },
            { id: "shopDays", label: "ShopDays", type: "uint8" },
            { id: "eventShopDays", label: "EventDays", type: "uint8" },
            { id: "basic", label: "Atq Básico (hit1)", type: "int16" },
            { id: "atqL", label: "Atq Lejano (hit2)", type: "int16" },
            { id: "atqE", label: "Atq Especial (hit3)", type: "int16" },
            { id: "atqLl", label: "Atq Llave (hit4)", type: "int16" },
            { id: "destCell", label: "Destrucción Cell", type: "int16" },
            { id: "armor", label: "Armadura", type: "int16" },
            { id: "energia", label: "Energía (HP)", type: "int16" },
            { id: "ptsCell", label: "Max Cell Points", type: "int16" },
            { id: "aspd", label: "Vel. Ataque", type: "float32" },
            { id: "mspd", label: "Vel. Movimiento", type: "float32" },
            { id: "cp", label: "CP", type: "uint16" },
            { id: "chaos", label: "Chaos", type: "uint8" },
            { id: "chaosTime", label: "Tiempo Chaos", type: "uint8" },
            { id: "tex1", label: "Textura 1", type: "file", fw: true },
            { id: "tex2", label: "Textura 2", type: "file", fw: true },
            { id: "script", label: "Script (.lua)", type: "file", fw: true },
            { id: "desc", label: "Descripción", type: "string", fw: true }
        ];

        document.getElementById('xfsInput').addEventListener('change', async function(e) {
            const files = e.target.files;
            if (files.length === 0) return;
            const status = document.getElementById('xfsStatus');
            status.innerHTML = `⏳ Leyendo ${files.length} archivo(s)...`;
            for (let file of files) {
                try {
                    const buffer = await file.arrayBuffer();
                    const data = new Uint8Array(buffer);
                    const dv = new DataView(buffer);
                    if (data.length < 10) continue;
                    const metaOff = dv.getUint32(0, true);
                    const zsize = data[metaOff];
                    const hRawComp = data.subarray(metaOff + 1, metaOff + 1 + zsize);
                    let hRaw = pako.inflate(hRawComp);
                    if (hRaw[0] !== 88 || hRaw[1] !== 70 || hRaw[2] !== 83 || hRaw[3] !== 50) continue;
                    const hdv = new DataView(hRaw.buffer, hRaw.byteOffset, hRaw.byteLength);
                    const filesCount = hdv.getUint32(8, true);
                    const infoPos = metaOff + 1 + zsize;
                    const iSize = data[infoPos] | (data[infoPos+1] << 8) | (data[infoPos+2] << 16);
                    const iRawComp = data.subarray(infoPos + 3, infoPos + 3 + iSize);
                    let iRaw = pako.inflate(iRawComp);
                    const idv = new DataView(iRaw.buffer, iRaw.byteOffset, iRaw.byteLength);
                    const decoder = new TextDecoder('windows-1252');
                    xfsBuffers.push(data);
                    const bufIdx = xfsBuffers.length - 1;
                    for (let i = 0; i < filesCount; i++) {
                        const entryOffset = i * 128;
                        let endStr = entryOffset;
                        while(endStr < entryOffset + 112 && iRaw[endStr] !== 0) { endStr++; }
                        const name = decoder.decode(iRaw.subarray(entryOffset, endStr));
                        const off = idv.getUint32(entryOffset + 112, true);
                        const comp = idv.getUint32(entryOffset + 116, true);
                        const ucSize = idv.getUint32(entryOffset + 120, true);
                        const cSize = idv.getUint32(entryOffset + 124, true);
                        if(name.trim() !== "") xfsRegistry.set(name.toLowerCase(), { bufferIdx: bufIdx, offset: off, comp: comp, ucSize: ucSize, cSize: cSize });
                    }
                } catch(err) { console.error("Error XFS:", err); }
            }
            const datalist = document.getElementById('xfs-datalist');
            datalist.innerHTML = '';
            xfsRegistry.forEach((val, path) => { let option = document.createElement('option'); option.value = path; datalist.appendChild(option); });
            status.innerHTML = `✔️ ${xfsRegistry.size} Rutas`;
            status.style.color = "var(--success)";
            if(currentIdx !== -1) loadItem(currentIdx);
        });

        function decodeXFSChunks(blob, ucTotal) {
            let result = new Uint8Array(ucTotal);
            let resPtr = 0; let p = 0;
            let dv = new DataView(blob.buffer, blob.byteOffset, blob.byteLength);
            while (resPtr < ucTotal && p + 3 <= blob.length) {
                let marker = blob[p + 2];
                if (marker === 0xc0) {
                    let rawLen = dv.getUint16(p, true);
                    result.set(blob.subarray(p + 5, p + 5 + rawLen), resPtr);
                    resPtr += rawLen; p += 5 + rawLen;
                } else if (marker === 0x80) {
                    let zlen = dv.getUint16(p + 3, true);
                    let decomp = pako.inflate(blob.subarray(p + 8, p + 8 + zlen));
                    result.set(decomp, resPtr);
                    resPtr += decomp.length; p += 8 + zlen;
                } else if (marker === 0x00) {
                    let zlen = dv.getUint32(p, true);
                    let decomp = pako.inflate(blob.subarray(p + 5, p + 5 + zlen));
                    result.set(decomp, resPtr);
                    resPtr += decomp.length; p += 5 + zlen;
                } else break;
            }
            return result;
        }

        function openCodeEditor(inputId) {
            const path = document.getElementById(inputId).value.trim();
            if (!path || path === "0") { alert("Ruta vacía."); return; }
            const entry = xfsRegistry.get(path.toLowerCase());
            if (!entry) { alert("Archivo no en XFS."); return; }
            document.getElementById('codeEditorTitle').innerText = path;
            document.getElementById('codeEditorTextarea').value = "Leyendo...";
            document.getElementById('codeEditorModal').style.display = 'flex';
            setTimeout(() => {
                try {
                    const blob = xfsBuffers[entry.bufferIdx].subarray(entry.offset, entry.offset + entry.cSize);
                    let ucData = (entry.comp === 1) ? decodeXFSChunks(blob, entry.ucSize) : blob;
                    document.getElementById('codeEditorTextarea').value = new TextDecoder('windows-1252').decode(ucData);
                } catch(e) { document.getElementById('codeEditorTextarea').value = "Error: " + e.message; }
            }, 50);
        }

        function downloadEditedCode() {
            const content = document.getElementById('codeEditorTextarea').value;
            const path = document.getElementById('codeEditorTitle').innerText;
            let buf = new Uint8Array(content.length);
            for(let i=0; i<content.length; i++) buf[i] = content.charCodeAt(i) < 256 ? content.charCodeAt(i) : 63;
            let blob = new Blob([buf], { type: "application/octet-stream" });
            let link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = path.split('\\').pop();
            link.click();
        }

        function closeCodeEditor() { document.getElementById('codeEditorModal').style.display = 'none'; }
        function validateXfsPath(inp) { if (xfsRegistry.size === 0) return; let val = inp.value.trim().toLowerCase(); inp.classList.remove('valid-path', 'invalid-path'); if (val !== "" && val !== "0") { if (!xfsRegistry.has(val)) inp.classList.add('invalid-path'); else inp.classList.add('valid-path'); } }
        function updateVisualizer(itemId, textureView) { let imgId = parseInt(textureView) > 0 ? parseInt(textureView) : parseInt(itemId); let basePath = document.getElementById('iconBasePath').value.trim(); if(!basePath.endsWith('/')) basePath += '/'; document.getElementById('itemPreviewImg').src = basePath + imgId + ".png"; }

        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0]; if (!file) return;
            const reader = new FileReader(); reader.onload = function(evt) { fileData = new Uint8Array(evt.target.result); indexItems(); };
            reader.readAsArrayBuffer(file);
        });

        function readNullTerminatedString(data, offset) { let start = offset; while (offset < data.length && data[offset] !== 0) { offset++; } let str = ""; if (offset > start) str = new TextDecoder('windows-1252').decode(data.subarray(start, offset)); return { str: str, next: offset + 1 }; }

        function indexItems() {
            parsedItems = []; let ptr = 2; let dv = new DataView(fileData.buffer);
            while (ptr < fileData.length - 20) {
                try {
                    let hasPrefix = false; if (fileData[ptr] === 0xBA && fileData[ptr+1] === 0x36) { hasPrefix = true; ptr += 2; }
                    let itemId = dv.getUint16(ptr, true); ptr += 2; let position = dv.getUint16(ptr, true); ptr += 2; ptr += 2; let textureView = dv.getUint16(ptr, true); ptr += 2; ptr += 2; 
                    let resName = readNullTerminatedString(fileData, ptr); let name = resName.str; ptr = resName.next;
                    let transparency = fileData[ptr++]; let colorHex = rgbToHex(fileData[ptr], fileData[ptr+1], fileData[ptr+2]); ptr += 3;
                    let resModel = readNullTerminatedString(fileData, ptr); let pathModel = resModel.str; ptr = resModel.next;
                    let cls = fileData[ptr++]; let level = fileData[ptr++]; let gold = dv.getUint32(ptr, true); ptr += 4; let cash = dv.getUint32(ptr, true); ptr += 4;
                    let shop = fileData[ptr++]; let power = fileData[ptr++]; let itemType = fileData[ptr++]; let unknow = fileData[ptr++]; let shopDays = fileData[ptr++]; let eventShopDays = fileData[ptr++];
                    let basic = dv.getInt16(ptr, true); ptr += 2; let atqL = dv.getInt16(ptr, true); ptr += 2; let atqE = dv.getInt16(ptr, true); ptr += 2; let atqLl = dv.getInt16(ptr, true); ptr += 2; let destCell = dv.getInt16(ptr, true); ptr += 2; let armor = dv.getInt16(ptr, true); ptr += 2; let energia = dv.getInt16(ptr, true); ptr += 2; let ptsCell = dv.getInt16(ptr, true); ptr += 2;
                    let aspd = dv.getFloat32(ptr, true); ptr += 4; let mspd = dv.getFloat32(ptr, true); ptr += 4;
                    let cp = dv.getUint16(ptr, true); ptr += 2; let chaos = fileData[ptr++]; let chaosTime = fileData[ptr++]; ptr++; 
                    let rTex1 = readNullTerminatedString(fileData, ptr); ptr = rTex1.next; let rTex2 = readNullTerminatedString(fileData, ptr); ptr = rTex2.next; let rScript = readNullTerminatedString(fileData, ptr); ptr = rScript.next; let rDesc = readNullTerminatedString(fileData, ptr); ptr = rDesc.next;
                    parsedItems.push({ visible: true, hasPrefix, itemId, position, textureView, name, transparency, colorHex, pathModel, cls, level, gold, cash, shop, power, itemType, unknow, shopDays, eventShopDays, basic, atqL, atqE, atqLl, destCell, armor, energia, ptsCell, aspd, mspd, cp, chaos, chaosTime, tex1: rTex1.str, tex2: rTex2.str, script: rScript.str, desc: rDesc.str });
                } catch (e) { break; }
            }
            parsedItems.sort((a, b) => a.itemId - b.itemId); updateUIState(); if(parsedItems.length > 0) loadItem(0);
        }

        function updateUIState() { document.getElementById('statusText').innerHTML = `Memoria: <span style="color:#00ff00">${parsedItems.length} ítems</span>`; const buttons = ['btnSave', 'btnSql', 'sep1', 'btnAdd', 'btnDup', 'btnDel', 'filtersBar']; buttons.forEach(id => document.getElementById(id).style.display = (id==='filtersBar' || id==='sep1') ? 'flex' : 'inline-flex'); buildSidebar(); }

        async function syncDB() {
            if (parsedItems.length === 0) return; if (!confirm("¿Sincronizar MySQL?")) return;
            const btn = document.getElementById('btnSql'); btn.innerHTML = "⏳..."; btn.disabled = true;
            try {
                const response = await fetch(window.location.href, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'sync_db', items: parsedItems }) });
                const result = await response.json();
                if (result.success) btn.innerHTML = "✔️ OK"; else alert(result.message);
            } catch (error) { btn.innerHTML = "❌ ERR"; }
            setTimeout(() => { btn.innerHTML = "⚡ SINCRONIZAR DB"; btn.disabled = false; }, 4000);
        }

        function applyFilters() {
            const qName = document.getElementById('filterName').value.toLowerCase();
            const qClass = document.getElementById('filterClass').value;
            const qType = document.getElementById('filterType').value;
            const ul = document.getElementById('itemList'); const lis = ul.getElementsByTagName('li');
            let firstVisibleIndex = -1;
            for (let i = 0; i < parsedItems.length; i++) {
                let show = true;
                if (qName && !(parsedItems[i].name || "").toLowerCase().includes(qName)) show = false;
                if (qClass !== "" && parsedItems[i].cls !== parseInt(qClass)) show = false;
                if (qType !== "" && parsedItems[i].itemType !== parseInt(qType)) show = false;
                parsedItems[i].visible = show; lis[i].style.display = show ? "block" : "none";
                if (show && firstVisibleIndex === -1) firstVisibleIndex = i;
            }
            if (firstVisibleIndex !== -1 && !parsedItems[currentIdx]?.visible) loadItem(firstVisibleIndex);
        }

        function buildSidebar() {
            const ul = document.getElementById('itemList'); ul.innerHTML = '';
            parsedItems.forEach((item, i) => {
                let li = document.createElement('li');
                li.innerHTML = `<span class="id-badge">[${item.itemId}]</span> ${item.name || 'Sin Nombre'}`;
                li.id = "li-" + i; li.onclick = () => loadItem(i);
                if (!item.visible) li.style.display = "none";
                ul.appendChild(li);
            });
            if (currentIdx !== -1 && document.getElementById('li-' + currentIdx)) document.getElementById('li-' + currentIdx).classList.add('active');
        }

        function buildForm() {
            const grid = document.getElementById('formGrid'); if(grid.innerHTML !== '') return; 
            FIELDS.forEach(f => {
                let wrap = document.createElement('div'); wrap.className = 'form-group' + (f.fw ? ' full-width' : '');
                let lbl = document.createElement('label'); lbl.innerText = f.label; wrap.appendChild(lbl);
                let inp = document.createElement('input'); inp.id = 'inp_' + f.id;
                if (f.type === 'color') inp.type = 'color';
                else { inp.type = 'text'; if(f.type !== 'string' && f.type !== 'file') inp.type = 'number'; }
                if(f.type === 'file') inp.setAttribute('list', 'xfs-datalist');
                inp.addEventListener('input', (e) => {
                    let val = e.target.value;
                    if(f.type === 'string' || f.type === 'file') parsedItems[currentIdx][f.id] = val;
                    else if (f.type === 'color') parsedItems[currentIdx][f.id] = val.replace('#', '').toUpperCase();
                    else if (f.type === 'float32') parsedItems[currentIdx][f.id] = parseFloat(val) || 0;
                    else parsedItems[currentIdx][f.id] = parseInt(val) || 0;
                    if (f.id === 'name' || f.id === 'itemId') buildSidebar();
                    if (f.type === 'file') validateXfsPath(inp);
                    if (f.id === 'textureView' || f.id === 'itemId') updateVisualizer(parsedItems[currentIdx].itemId, parsedItems[currentIdx].textureView);
                });
                wrap.appendChild(inp);
                if(f.type === 'file') {
                    let btnEdit = document.createElement('button'); btnEdit.className = 'btn-edit-code'; btnEdit.innerText = '✏️ EDITAR'; btnEdit.onclick = () => openCodeEditor('inp_' + f.id); wrap.appendChild(btnEdit);
                }
                grid.appendChild(wrap);
            });
        }

        function loadItem(index) {
            if (index < 0 || index >= parsedItems.length) return;
            if (currentIdx !== -1 && document.getElementById('li-' + currentIdx)) document.getElementById('li-' + currentIdx).classList.remove('active');
            currentIdx = index;
            let newLi = document.getElementById('li-' + currentIdx);
            if(newLi) { newLi.classList.add('active'); newLi.scrollIntoView({ block: 'nearest' }); }
            document.getElementById('editorArea').style.display = 'block';
            buildForm(); let item = parsedItems[index];
            document.getElementById('itemHeaderTitle').innerHTML = `Editando: <span>[${item.itemId}]</span> ${item.name}`;
            updateVisualizer(item.itemId, item.textureView);
            FIELDS.forEach(f => {
                let inp = document.getElementById('inp_' + f.id);
                if(f.type === 'float32') inp.value = item[f.id].toFixed(3);
                else if (f.type === 'color') inp.value = '#' + (item[f.id] || "FFFFFF");
                else inp.value = item[f.id];
                if (f.type === 'file') validateXfsPath(inp);
            });
        }

        function navigate(step) { let newIdx = currentIdx + step; if(newIdx >= 0 && newIdx < parsedItems.length) loadItem(newIdx); }
        function addNewItem() { let maxId = 0; parsedItems.forEach(i => { if(i.itemId > maxId) maxId = i.itemId; }); let newItem = { visible: true, itemId: maxId + 1, name: "New", transparency: 255, colorHex: "FFFFFF", pathModel: "ModelsSV\\", cls: 31, level: 1, gold: 0, cash: 0, shop: 1, power: 0, itemType: 0, basic: 0, aspd: 1.0, mspd: 1.0, cp: 0, tex1: "0", tex2: "0", script: "0", desc: "..." }; parsedItems.push(newItem); parsedItems.sort((a,b)=>a.itemId-b.itemId); buildSidebar(); loadItem(parsedItems.findIndex(i=>i.itemId===newItem.itemId)); }
        function duplicateItem() { let maxId = 0; parsedItems.forEach(i => { if(i.itemId > maxId) maxId = i.itemId; }); let dup = JSON.parse(JSON.stringify(parsedItems[currentIdx])); dup.itemId = maxId + 1; dup.name += " (Copy)"; parsedItems.push(dup); parsedItems.sort((a,b)=>a.itemId-b.itemId); buildSidebar(); loadItem(parsedItems.findIndex(i=>i.itemId===dup.itemId)); }
        function deleteItem() { if (!confirm("¿Borrar?")) return; parsedItems.splice(currentIdx, 1); buildSidebar(); loadItem(0); }
        function rgbToHex(r, g, b) { return ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase(); }
        function hexToRgb(hex) { let bigint = parseInt(hex.replace('#',''), 16); return [(bigint >> 16) & 255, (bigint >> 8) & 255, bigint & 255]; }

        function saveChanges() {
            let maxId = 0; parsedItems.forEach(i => { if(i.itemId > maxId) maxId = i.itemId; });
            let newFile = [maxId & 0xFF, (maxId >> 8) & 0xFF];
            parsedItems.forEach(item => { let block = rebuildBlock(item); for(let b of block) newFile.push(b); });
            let blob = new Blob([new Uint8Array(newFile)], { type: "application/octet-stream" });
            let a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = "items.dat"; a.click();
        }
    </script>
</body>
</html>