<?php
// ==============================================================================
// BACKEND PHP: Sincronización DB npcinfo
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data && isset($data['action']) && $data['action'] === 'sync_db') {
        header('Content-Type: application/json');
        $host = "127.0.0.1"; $user = "root"; $pass = "1234567"; $db = "rakion";
        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) { echo json_encode(['success' => false, 'message' => 'Error MySQL']); exit; }
        
        $conn->set_charset("utf8");
        $conn->query("TRUNCATE TABLE npcinfo"); // Limpieza de tabla[cite: 6]
        
        $stmt = $conn->prepare("INSERT INTO npcinfo (npc, level, exp, gold) VALUES (?, ?, ?, ?)");
        $count = 0;
        foreach ($data['creatures'] as $npcIdx => $creature) {
            foreach ($creature['levels'] as $lvlIdx => $stats) {
                $level = $lvlIdx + 1;
                $exp = (int)$stats['exp'];
                $gold = (int)$stats['price']; // Return_Price -> gold[cite: 5, 6]
                $stmt->bind_param("iiii", $npcIdx, $level, $exp, $gold);
                $stmt->execute();
                $count++;
            }
        }
        $stmt->close(); $conn->close();
        echo json_encode(['success' => true, 'message' => "Insertadas $count filas en npcinfo."]); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Neon Creature Studio Pro | Rakion</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pako/2.1.0/pako.min.js"></script>
    <style>
        :root { --bg: #0a0a0f; --bg-sec: #121218; --primary: #00d4ff; --text: #e0e0e0; --warning: #ffcc00; --danger: #ff0055; --success: #00ff88; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        
        .toolbar { background: var(--bg-sec); padding: 15px 20px; display: flex; gap: 12px; align-items: center; border-bottom: 1px solid #333; z-index: 10; }
        .toolbar button { color: #000; border: none; padding: 8px 16px; font-weight: bold; cursor: pointer; border-radius: 4px; transition: 0.2s; text-transform: uppercase; font-size: 11px; }
        .btn-blue { background: var(--primary); }
        .btn-green { background: var(--success); }
        .btn-sql { background: #ff7700; }

        .main-container { display: flex; flex-grow: 1; overflow: hidden; }
        .sidebar { width: 340px; background: var(--bg-sec); border-right: 1px solid #222; display: flex; flex-direction: column; }
        .sidebar-list { flex-grow: 1; overflow-y: auto; list-style: none; margin: 0; padding: 0; }
        .sidebar-list li { padding: 12px 15px; border-bottom: 1px solid #222; cursor: pointer; font-size: 13px; font-family: Consolas; color: #888; }
        .sidebar-list li.active { background: rgba(0, 212, 255, 0.1); border-left: 4px solid var(--primary); color: #fff; }

        .editor-area { flex-grow: 1; padding: 30px; overflow-y: auto; background: var(--bg); position: relative; }
        
        .header-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .creature-title { margin: 0; color: #fff; font-weight: 400; font-size: 24px; }
        .creature-title span { color: var(--primary); font-weight: bold; }

        .preview-box { width: 100px; height: 100px; background: rgba(0,0,0,0.5); border: 2px solid var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 0 20px rgba(0, 212, 255, 0.2); }
        .preview-box img { max-width: 90%; max-height: 90%; filter: drop-shadow(0 0 5px var(--primary)); }

        .level-selector { background: #1a1a25; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #333; display: flex; align-items: center; gap: 20px; }
        .level-selector input { flex: 1; accent-color: var(--primary); }
        .lvl-badge { background: var(--primary); color: #000; padding: 5px 15px; border-radius: 4px; font-weight: bold; font-family: Consolas; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 15px; }
        .form-group { display: flex; flex-direction: column; background: rgba(255,255,255,0.02); padding: 10px; border-radius: 6px; border: 1px solid #222; }
        .form-group label { color: var(--primary); font-size: 10px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
        .form-group input { background: #000; border: 1px solid #333; color: white; padding: 8px; font-family: Consolas; border-radius: 4px; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn-blue" onclick="document.getElementById('datInput').click()">📁 Cargar Creatures.dat</button>
        <button class="btn-green" id="btnSave" style="display:none" onclick="saveDat()">💾 Guardar .DAT</button>
        <button class="btn-sql" id="btnSync" style="display:none" onclick="syncDB()">⚡ Sincronizar npcinfo</button>
        
        <div style="margin-left: auto; display: flex; align-items: center; gap: 10px;">
            <label style="font-size: 11px; color: var(--primary); font-weight: bold;">URL ICONOS:</label>
            <input type="text" id="iconBasePath" value="/rakion/paneluser/modules/img/items/" style="background:#0a0a0f; color:#fff; border:1px solid #444; padding:5px; border-radius:3px; font-family:Consolas; width: 220px;">
        </div>
        
        <input type="file" id="datInput" accept=".dat" style="display:none">
    </div>

    <div class="main-container">
        <div class="sidebar">
            <ul class="sidebar-list" id="cList"></ul>
        </div>
        <div class="editor-area" id="editorArea" style="display:none;">
            
            <div class="header-container">
                <h2 class="creature-title" id="headerTitle"></h2>
                <div class="preview-box">
                    <img id="creatureImg" src="" alt="Icono" onerror="this.style.opacity='0'" onload="this.style.opacity='1'">
                </div>
            </div>
            
            <div class="level-selector">
                <label>Nivel de Stats:</label>
                <input type="range" id="lvlRange" min="1" max="99" value="1" oninput="updateLvl(this.value)">
                <div class="lvl-badge" id="lvlDisp">1</div>
            </div>

            <div id="formGrid" class="form-grid"></div>
        </div>
    </div>

<script>
    // Mapeo de Nombres reales basados en iteminfo[cite: 1]
    const CREATURE_DATA_MAP = [
        { id: 8000, name: "Nak" }, { id: 8001, name: "Panzer" }, { id: 8002, name: "Crossbow" }, 
        { id: 8003, name: "Blazer" }, { id: 8004, name: "Golem" }, { id: 8005, name: "SoulCannon" }, 
        { id: 8006, name: "Longbow" }, { id: 8007, name: "Taurus" }, { id: 8008, name: "IceWind" }, 
        { id: 8009, name: "Dragon" }, { id: 8010, name: "MasterGolem" }, { id: 8011, name: "GoldGolem" }, 
        { id: 8012, name: "BlackNak" }, { id: 8013, name: "BloodNak" }, { id: 8014, name: "WhiteNak" }, 
        { id: 8015, name: "BlackPenzer" }, { id: 8016, name: "AssaultPanzer" }, { id: 8017, name: "WhitePenzer" }, 
        { id: 8018, name: "BlackCrossbow" }, { id: 8019, name: "Crossbow2" }, { id: 8020, name: "Crossbow3" }, 
        { id: 8021, name: "BlackBlazer" }, { id: 8022, name: "SkyBlazer" }, { id: 8023, name: "WhiteBlazer" }, 
        { id: 8024, name: "Blazer2" }, { id: 8025, name: "IronGolem" }, { id: 8026, name: "Golem2" }, 
        { id: 8027, name: "Golem3" }, { id: 8028, name: "SoulCannon2" }, { id: 8029, name: "SoulCannon3" }, 
        { id: 8030, name: "SoulCannon4" }, { id: 8031, name: "LongBow2" }, { id: 8032, name: "LongBow3" }, 
        { id: 8033, name: "BlackTaurus" }, { id: 8034, name: "Taurus2" }, { id: 8035, name: "Taurus3" }, 
        { id: 8036, name: "BlackIceWind" }, { id: 8037, name: "IceWind2" }, { id: 8038, name: "IceWind3" }, 
        { id: 8039, name: "BlackDragon" }, { id: 8040, name: "WhiteDragon" }, { id: 8041, name: "Dragon2" }, 
        { id: 8042, name: "Dragon3" }, { id: 8043, name: "Dragon4" }, { id: 8044, name: "BlackDragon2" }, 
        { id: 8045, name: "BlackDragon3" }, { id: 8046, name: "Chocolate Cake" }
    ];

    const FIELDS = [
        { id: "color", label: "Color (RGBA)", type: "rgba", size: 4 },
        { id: "exp", label: "Experiencia", type: "int32", size: 4 },
        { id: "attack", label: "Ataque", type: "uint16", size: 2 },
        { id: "armor", label: "Armadura", type: "uint16", size: 2 },
        { id: "energy", label: "Energía", type: "uint16", size: 2 },
        { id: "unk1", label: "Unknow 1", type: "float", size: 4 },
        { id: "speed", label: "Velocidad", type: "float", size: 4 },
        { id: "unk2", label: "Unknow 2", type: "float", size: 4 },
        { id: "atk_spd", label: "Attack Speed", type: "float", size: 4 },
        { id: "vision", label: "Vision Range", type: "float", size: 4 },
        { id: "spd_dist", label: "Speed Distance", type: "float", size: 4 },
        { id: "unk3", label: "Unknow 3", type: "float", size: 4 },
        { id: "atk_spd_dist", label: "Atk Spd Dist", type: "float", size: 4 },
        { id: "unk4", label: "Unknow 4", type: "float", size: 4 },
        { id: "recovery", label: "Recovery Time", type: "float", size: 4 },
        { id: "unk5", label: "Unknow 5", type: "float", size: 4 },
        { id: "cell_dest", label: "Cell Dest", type: "uint16", size: 2 },
        { id: "cell_pt", label: "Cell Point", type: "uint16", size: 2 },
        { id: "unk7", label: "Unknow 7", type: "uint16", size: 2 },
        { id: "density", label: "Density", type: "float", size: 4 },
        { id: "price", label: "Return Price", type: "int32", size: 4 },
        { id: "unk11", label: "Unknow 11", type: "uint16", size: 2 },
        { id: "unk12", label: "Unknow 12", type: "int32", size: 4 },
        { id: "unk13", label: "Unknow 13", type: "int32", size: 4 }
    ];

    let creatures = []; let currentIdx = -1; let currentLvl = 0;

    document.getElementById('datInput').addEventListener('change', async (e) => {
        const file = e.target.files[0]; if(!file) return;
        const buf = await file.arrayBuffer();
        parseCreatures(buf);
        document.getElementById('btnSave').style.display = 'inline-block';
        document.getElementById('btnSync').style.display = 'inline-block';
    });

    function parseCreatures(buf) {
        const dv = new DataView(buf);
        let ptr = 0; creatures = [];
        for(let n=0; n < CREATURE_DATA_MAP.length; n++) {
            let creature = { id: CREATURE_DATA_MAP[n].id, name: CREATURE_DATA_MAP[n].name, levels: Array.from({length: 99}, () => ({})) };
            FIELDS.forEach(f => {
                for(let l=0; l < 99; l++) {
                    if (ptr >= buf.byteLength) break;
                    let val;
                    if(f.type === 'rgba') {
                        val = Array.from(new Uint8Array(buf, ptr, 4)).map(b => b.toString(16).padStart(2,'0')).join('').toUpperCase();
                        ptr += 4;
                    } else if(f.type === 'int32') { val = dv.getInt32(ptr, true); ptr += 4; }
                    else if(f.type === 'uint16') { val = dv.getUint16(ptr, true); ptr += 2; }
                    else if(f.type === 'float') { val = dv.getFloat32(ptr, true); ptr += 4; }
                    creature.levels[l][f.id] = val;
                }
            });
            creatures.push(creature);
        }
        renderList(); loadCreature(0);
    }

    function renderList() {
        const list = document.getElementById('cList'); list.innerHTML = '';
        creatures.forEach((c, i) => {
            const li = document.createElement('li');
            if(i === currentIdx) li.className = 'active';
            li.innerHTML = `<span class="id-badge">[${c.id}]</span> ${c.name}`;
            li.onclick = () => loadCreature(i);
            list.appendChild(li);
        });
    }

    function loadCreature(idx) {
        currentIdx = idx;
        document.querySelectorAll('.sidebar-list li').forEach((l, i) => l.className = i === idx ? 'active' : '');
        document.getElementById('editorArea').style.display = 'block';
        const c = creatures[idx];
        document.getElementById('headerTitle').innerHTML = `Criatura: <span>${c.name}</span>`;
        
        let basePath = document.getElementById('iconBasePath').value.trim();
        if(!basePath.endsWith('/')) basePath += '/';
        document.getElementById('creatureImg').src = basePath + c.id + ".png";
        
        renderStats();
    }

    function updateLvl(v) { currentLvl = v - 1; document.getElementById('lvlDisp').innerText = v; renderStats(); }

    function renderStats() {
        const grid = document.getElementById('formGrid'); grid.innerHTML = '';
        const data = creatures[currentIdx].levels[currentLvl];
        FIELDS.forEach(f => {
            const div = document.createElement('div'); div.className = 'form-group';
            let val = data[f.id];
            if(f.type === 'float') val = Number(val).toFixed(1);
            div.innerHTML = `<label>${f.label}</label><input type="text" value="${val}">`;
            div.querySelector('input').onchange = (e) => {
                let v = e.target.value;
                creatures[currentIdx].levels[currentLvl][f.id] = (f.type === 'float') ? parseFloat(v) : (f.type === 'rgba' ? v : parseInt(v));
            };
            grid.appendChild(div);
        });
    }

    async function syncDB() {
        if(!confirm("¿Sincronizar npcinfo en la DB?")) return;
        const btn = document.getElementById('btnSync'); btn.innerText = "⏳...";
        const res = await fetch(window.location.href, {
            method: 'POST',
            body: JSON.stringify({ action: 'sync_db', creatures: creatures })
        });
        const json = await res.json();
        if(json.success) btn.innerText = "✔️ SINCRONIZADO";
    }

    function saveDat() {
        const totalSize = creatures.length * 8118; // 82 bytes por bloque * 99 niveles[cite: 5]
        const buffer = new ArrayBuffer(totalSize);
        const dv = new DataView(buffer);
        const u8 = new Uint8Array(buffer);
        let ptr = 0;
        creatures.forEach(c => {
            FIELDS.forEach(f => {
                for(let l=0; l<99; l++) {
                    let val = c.levels[l][f.id];
                    if(f.type === 'rgba') {
                        let hex = val.match(/.{1,2}/g).map(h => parseInt(h, 16));
                        u8.set(hex, ptr); ptr += 4;
                    } else if(f.type === 'int32') { dv.setInt32(ptr, val, true); ptr += 4; }
                    else if(f.type === 'uint16') { dv.setUint16(ptr, val, true); ptr += 2; }
                    else if(f.type === 'float') { dv.setFloat32(ptr, val, true); ptr += 4; }
                }
            });
        });
        const blob = new Blob([buffer], { type: "application/octet-stream" });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob); link.download = "creatures.dat"; link.click();
    }
</script>
</body>
</html>