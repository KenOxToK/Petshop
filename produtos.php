<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$host = "localhost";
$db_name = "petshop";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo json_encode(["error" => "Erro de conexão: " . $exception->getMessage()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(["status" => "success", "methods" => "GET, POST, PUT, PATCH, DELETE, OPTIONS"]);
    exit();
}

$is_api = false;
if (isset($_GET['api']) || $method !== 'GET') {
    $is_api = true;
}

if ($is_api) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $conn->query("SELECT * FROM produtos");
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            echo json_encode($result == false ? [] : $result);
            break;
            
        case 'POST':
            if (!empty($input['nome']) && isset($input['preco']) && isset($input['estoque'])) {
                $stmt = $conn->prepare("INSERT INTO produtos (nome, preco, estoque) VALUES (?, ?, ?)");
                $stmt->execute([$input['nome'], $input['preco'], $input['estoque']]);
                echo json_encode(["message" => "Produto adicionado com sucesso!", "id" => $conn->lastInsertId()]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Dados incompletos no Body JSON."]);
            }
            break;
            
        case 'PUT':
            if (isset($_GET['id']) && !empty($input['nome']) && isset($input['preco']) && isset($input['estoque'])) {
                $stmt = $conn->prepare("UPDATE produtos SET nome = ?, preco = ?, estoque = ? WHERE id = ?");
                $stmt->execute([$input['nome'], $input['preco'], $input['estoque'], $_GET['id']]);
                echo json_encode(["message" => "Produto trocado por completo com sucesso!"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID na URL ou dados no Body faltando."]);
            }
            break;
            
        case 'PATCH':
            if (isset($_GET['id'])) {
                $fields = []; $params = [];
                if (isset($input['nome'])) { $fields[] = "nome = ?"; $params[] = $input['nome']; }
                if (isset($input['preco'])) { $fields[] = "preco = ?"; $params[] = $input['preco']; }
                if (isset($input['estoque'])) { $fields[] = "estoque = ?"; $params[] = $input['estoque']; }
                if (count($fields) > 0) {
                    $params[] = $_GET['id'];
                    $stmt = $conn->prepare("UPDATE produtos SET " . implode(", ", $fields) . " WHERE id = ?");
                    $stmt->execute($params);
                    echo json_encode(["message" => "Informação atualizada via PATCH com sucesso!"]);
                } else {
                    echo json_encode(["message" => "Nenhum campo informado."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID não fornecido na URL."]);
            }
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode(["message" => "Produto removido com sucesso!"]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "ID não fornecido na URL."]);
            }
            break;
    }
    exit();
}

header("Content-Type: text/html; charset=UTF-8");
$produtos = $conn->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Akatsuki Pet Shop API</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Rajdhani:wght@500;700&display=swap');
        body {
            background: #0a0a0c radial-gradient(circle at 50% 50%, #1a0505 0%, #0a0a0c 80%);
            color: #fff; font-family: 'Rajdhani', sans-serif; min-height: 100vh; overflow-x: hidden; margin: 0;
        }
        .neon-glow { text-shadow: 0 0 10px #ff0033, 0 0 20px #ff0033; }
        .neon-border { border: 2px solid #ff0033; box-shadow: 0 0 15px rgba(255, 0, 51, 0.4); }
        header { text-align: center; padding: 40px 20px; }
        header h1 { font-family: 'Orbitron', sans-serif; font-size: 3rem; letter-spacing: 4px; margin: 0 0 10px 0; }
        .akatsuki-cloud {
            width: 120px; height: 60px; background: #ff0033; position: absolute;
            border-radius: 50px; opacity: 0.12; animation: floatLoop 18s linear infinite; box-shadow: 0 0 20px #ff0033;
        }
        .cloud1 { top: 15%; left: -150px; }
        .cloud2 { top: 60%; left: -150px; animation-delay: 6s; width: 90px; height: 45px; }
        @keyframes floatLoop { 0% { transform: translateX(0vw); } 100% { transform: translateX(115vw); } }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; position: relative; z-index: 2; }
        .grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 30px; }
        .card { background: rgba(15, 15, 20, 0.85); backdrop-filter: blur(10px); border-radius: 12px; padding: 25px; }
        .card h2 { font-family: 'Orbitron', sans-serif; color: #ff0033; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid rgba(255,0,51,0.3); padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; }
        th { border-bottom: 2px solid #ff0033; color: #888; }
        tr:hover { background: rgba(255, 0, 51, 0.1); }
        .price-tag { color: #00ffcc; text-shadow: 0 0 5px #00ffcc; font-weight: bold; }
        .stock-tag { color: #ffcc00; text-shadow: 0 0 5px #ffcc00; font-weight: bold; }
        .method-list { padding: 0; margin: 0; }
        .method-list li { list-style: none; margin-bottom: 14px; padding: 10px; background: rgba(255,255,255,0.02); border-left: 4px solid #ff0033; border-radius: 0 6px 6px 0; }
        .badge { font-family: 'Orbitron', sans-serif; font-weight: bold; padding: 3px 8px; border-radius: 4px; margin-right: 8px; color: #000; display: inline-block; min-width: 65px; text-align: center; font-size: 0.85rem; }
        code { background: rgba(0,0,0,0.4); padding: 2px 6px; border-radius: 4px; color: #00ffcc; font-family: monospace; }
    </style>
</head>
<body>
    <div class="akatsuki-cloud cloud1"></div>
    <div class="akatsuki-cloud cloud2"></div>
    <header>
        <h1 class="neon-glow">AKATSUKI PET SHOP</h1>
        <p style="color: #aaa; letter-spacing: 2px; margin: 0;">SISTEMA REST API • TANIA</p>
    </header>
    <div class="container">
        <div class="grid">
            <div class="card neon-border">
                <h2>📦 PRODUTOS EM ESTOQUE</h2>
                <table>
                    <thead><tr><th>ID</th><th>NOME</th><th>PREÇO</th><th>ESTOQUE</th></tr></thead>
                    <tbody>
                        <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td>#<?= $p['id'] ?></td>
                            <td><strong><?= htmlspecialchars($p['nome']) ?></strong></td>
                            <td class="price-tag">R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                            <td class="stock-tag"><?= $p['estoque'] ?> un</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card neon-border">
                <h2>⛩️ GUIA DOS PEDIDOS (POSTMAN)</h2>
                <ul class="method-list">
                    <li><span class="badge" style="background:#ffcc00;">POST</span><code>/produtos.php</code><div style="font-size:0.9rem;color:#ccc;margin-top:5px;">1. Caminha para cachorro: <code>{"nome":"Caminha para cachorro","preco":79.90,"estoque":6}</code></div></li>
                    <li><span class="badge" style="background:#ff00ff;">PUT</span><code>/produtos.php?id=2</code><div style="font-size:0.9rem;color:#ccc;margin-top:5px;">2. Guia retrátil no lugar da Coleira (ID 2): <code>{"nome":"Guia retrátil","preco":45.00,"estoque":8}</code></div></li>
                    <li><span class="badge" style="background:#00ffcc;">GET</span><code>/produtos.php?api=1</code><div style="font-size:0.9rem;color:#ccc;margin-top:5px;">3. Conferir se a caminha entrou e se a guia ficou no lugar.</div></li>
                    <li><span class="badge" style="background:#3399ff;">PATCH</span><code>/produtos.php?id=14</code><div style="font-size:0.9rem;color:#ccc;margin-top:5px;">4. Mudar estoque da Caminha (ID 14) para 9: <code>{"estoque":9}</code></div></li>
                    <li><span class="badge" style="background:#ff3333;color:#fff;">DELETE</span><code>/produtos.php?id=6</code><div style="font-size:0.9rem;color:#ccc;margin-top:5px;">5. Remove Brinquedo de corda (ID 6).</div></li>
                    <li><span class="badge" style="background:#fff;">OPTIONS</span><code>/produtos.php</code><div style="font-size:0.9rem;color:#ccc;margin-top:5px;">6. Consultar métodos permitidos sem mexer em nada.</div></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>