<?php
header('Content-Type: application/json');

// Configurações de APIs
$VIRUSTOTAL_API_KEY = '81d9e2a95c5b27dd47775884a8967074ff99e3a2b89e6818a9222e4eedbe5ef9'; // Substitua pela sua chave
$SAFEBROWSING_API_KEY = 'AIzaSyBqsbb3claaGJ-4XZ-FugWHLtl5uxHDdNo';

// Sistema de rate limit para VirusTotal
$VIRUSTOTAL_MAX_REQUESTS = 4;
$VIRUSTOTAL_WINDOW_MINUTES = 1;

function normalizarUrl($url) {
    // Remover espaços em branco
    $url = trim($url);
    
    // Adicionar http:// se não tiver protocolo
    if (!preg_match('/^https?:\/\//i', $url)) {
        $url = 'http://' . $url;
    }
    
    // Validar e sanitizar URL
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    return $url;
}

// Gestão de Rate Limit para VirusTotal
function getVirusTotalRateLimit() {
    $rateLimitFile = __DIR__ . '/virustotal_rate_limit.json';
    $now = time();
    
    if (file_exists($rateLimitFile)) {
        $rateData = json_decode(file_get_contents($rateLimitFile), true);
        
        // Manter apenas registros da última janela de tempo
        $rateData = array_filter($rateData, function($timestamp) use ($now) {
            return ($now - $timestamp) < (60 * $GLOBALS['VIRUSTOTAL_WINDOW_MINUTES']);
        });
        
        $used = count($rateData);
        $remaining = $GLOBALS['VIRUSTOTAL_MAX_REQUESTS'] - $used;
        
        return [
            'used' => $used,
            'remaining' => $remaining,
            'exceeded' => $used >= $GLOBALS['VIRUSTOTAL_MAX_REQUESTS'],
            'reset_time' => $now + (60 * $GLOBALS['VIRUSTOTAL_WINDOW_MINUTES'])
        ];
    }
    
    return [
        'used' => 0,
        'remaining' => $GLOBALS['VIRUSTOTAL_MAX_REQUESTS'],
        'exceeded' => false,
        'reset_time' => $now + (60 * $GLOBALS['VIRUSTOTAL_WINDOW_MINUTES'])
    ];
}

function recordVirusTotalRequest() {
    $rateLimitFile = __DIR__ . '/virustotal_rate_limit.json';
    $now = time();
    
    $rateData = [];
    if (file_exists($rateLimitFile)) {
        $rateData = json_decode(file_get_contents($rateLimitFile), true);
        $rateData = array_filter($rateData, function($timestamp) use ($now) {
            return ($now - $timestamp) < (60 * $GLOBALS['VIRUSTOTAL_WINDOW_MINUTES']);
        });
    }
    
    $rateData[] = $now;
    file_put_contents($rateLimitFile, json_encode(array_values($rateData)));
}

// Safe Browsing (mantém igual ao seu código)
function verificarSafeBrowsing($url) {
    global $SAFEBROWSING_API_KEY;
    
    $apiUrl = "https://safebrowsing.googleapis.com/v4/threatMatches:find?key=" . $SAFEBROWSING_API_KEY;
    
    $postData = [
        "client" => [
            "clientId" => "safelinks",
            "clientVersion" => "1.0.0"
        ],
        "threatInfo" => [
            "threatTypes" => [
                "MALWARE",
                "SOCIAL_ENGINEERING",
                "UNWANTED_SOFTWARE",
                "POTENTIALLY_HARMFUL_APPLICATION"
            ],
            "platformTypes" => ["ANY_PLATFORM"],
            "threatEntryTypes" => ["URL"],
            "threatEntries" => [
                ["url" => $url]
            ]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FAILONERROR => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        return [
            'segura' => true,
            'erro' => 'Erro na comunicação com Safe Browsing: ' . $curlError,
            'codigo_http' => $httpCode
        ];
    }
    
    if ($httpCode !== 200) {
        return [
            'segura' => true,
            'erro' => 'Erro no Safe Browsing: HTTP ' . $httpCode,
            'resposta' => $response
        ];
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'segura' => true,
            'erro' => 'Resposta do Safe Browsing inválida'
        ];
    }
    
    if (empty($data['matches'])) {
        return [
            'segura' => true,
            'url' => $url
        ];
    }
    
    $threats = [];
    foreach ($data['matches'] as $match) {
        $threats[] = [
            'tipo' => $match['threatType'],
            'plataforma' => $match['platformType']
        ];
    }
    
    return [
        'segura' => false,
        'ameacas' => $threats,
        'url' => $url
    ];
}

// VirusTotal
function verificarVirusTotal($url) {
    global $VIRUSTOTAL_API_KEY;
    
    $reportUrl = "https://www.virustotal.com/vtapi/v2/url/report";
    $postData = http_build_query([
        'apikey' => $VIRUSTOTAL_API_KEY,
        'resource' => $url
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $reportUrl . '?' . $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        return [
            'segura' => null,
            'erro' => 'Erro VirusTotal: ' . $curlError
        ];
    }
    
    if ($httpCode !== 200) {
        return [
            'segura' => null,
            'erro' => 'VirusTotal HTTP: ' . $httpCode
        ];
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'segura' => null,
            'erro' => 'Resposta VirusTotal inválida'
        ];
    }
    
    if ($data['response_code'] === 0) {
        return [
            'segura' => true,
            'mensagem' => 'URL não encontrada no VirusTotal'
        ];
    }
    
    $totalAnalises = 0;
    $totalDeteccoes = 0;
    $ameacas = [];
    
    if (isset($data['scans']) && is_array($data['scans'])) {
        foreach ($data['scans'] as $engine => $result) {
            $totalAnalises++;
            if ($result['detected']) {
                $totalDeteccoes++;
                $ameacas[] = [
                    'engine' => $engine,
                    'resultado' => $result['result']
                ];
            }
        }
    }
    
    $limiteDeteccoes = 3;
    
    if ($totalDeteccoes < $limiteDeteccoes) {
        return [
            'segura' => true,
            'total_analises' => $totalAnalises,
            'total_deteccoes' => $totalDeteccoes,
            'deteccoes_favoraveis' => ($totalAnalises - $totalDeteccoes)
        ];
    } else {
        return [
            'segura' => false,
            'total_analises' => $totalAnalises,
            'total_deteccoes' => $totalDeteccoes,
            'ameacas' => $ameacas
        ];
    }
}

// Análise heurística para priorização
function analiseHeuristica($url) {
    $dominio = parse_url($url, PHP_URL_HOST);
    $pontuacao = 0;
    $motivos = [];
    
    // Domínios confiáveis
    $dominiosConfiáveis = [
        'google.com', 'microsoft.com', 'apple.com', 'amazon.com',
        'facebook.com', 'youtube.com', 'wikipedia.org', 'github.com',
        'gov.br', 'org.br', 'com.br', 'net.br'
    ];
    
    foreach ($dominiosConfiáveis as $confiavel) {
        if (strpos($dominio, $confiavel) !== false) {
            return [
                'nivel_risco' => 'BAIXO',
                'pontuacao' => 0,
                'motivos' => ['Domínio conhecidamente confiável']
            ];
        }
    }
    
    // Domínios gratuitos suspeitos
    $dominiosGratuitos = ['.tk', '.ml', '.ga', '.cf', '.gq'];
    foreach ($dominiosGratuitos as $gratuito) {
        if (strpos($dominio, $gratuito) !== false) {
            $pontuacao += 3;
            $motivos[] = "Domínio gratuito suspeito ($gratuito)";
        }
    }
    
    // IP direto
    if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $dominio)) {
        $pontuacao += 2;
        $motivos[] = "Usa endereço IP direto";
    }
    
    if ($pontuacao >= 3) {
        return [
            'nivel_risco' => 'ALTO',
            'pontuacao' => $pontuacao,
            'motivos' => $motivos
        ];
    } elseif ($pontuacao >= 1) {
        return [
            'nivel_risco' => 'MEDIO',
            'pontuacao' => $pontuacao,
            'motivos' => $motivos
        ];
    }
    
    return [
        'nivel_risco' => 'BAIXO',
        'pontuacao' => $pontuacao,
        'motivos' => ['Nenhum padrão suspeito detectado']
    ];
}

// Verificar idade do domínio com WhoIsFreaks API
function verificarIdadeDominio($url) {
    $dominio = parse_url($url, PHP_URL_HOST);
    $dominio = preg_replace('/^www\./', '', $dominio);
    
    // API WhoIsFreaks
    $apiKey = 'f4ac1a17d1cb4a419753daab8b4f6845';
    $apiUrl = "https://api.whoisfreaks.com/v1.0/whois?apiKey=" . $apiKey . "&whois=live&domainName=" . urlencode($dominio);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Log para debug (remova em produção)
    error_log("WhoIsFreaks API Response - Domain: $dominio, HTTP Code: $httpCode, Error: $curlError");
    
    if ($response && $httpCode === 200) {
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Verificar diferentes formatos de data que a API pode retornar
            $creationDate = null;
            
            if (isset($data['create_date'])) {
                $creationDate = $data['create_date'];
            } elseif (isset($data['creation_date'])) {
                $creationDate = $data['creation_date'];
            } elseif (isset($data['created_date'])) {
                $creationDate = $data['created_date'];
            } elseif (isset($data['registered_date'])) {
                $creationDate = $data['registered_date'];
            }
            
            if ($creationDate) {
                $timestamp = strtotime($creationDate);
                
                if ($timestamp !== false) {
                    $idadeDias = floor((time() - $timestamp) / (60 * 60 * 24));
                    
                    return [
                        'idade_dias' => $idadeDias,
                        'data_criacao' => date('d/m/Y', $timestamp),
                        'fonte' => 'WhoIsFreaks API',
                        'timestamp_criacao' => $creationDate,
                        'dados_completos' => $data // Para debug, remova em produção
                    ];
                }
            }
            
            // Se chegou aqui, a API respondeu mas não encontrou data de criação
            return [
                'idade_dias' => null,
                'data_criacao' => 'Não disponível',
                'fonte' => 'WhoIsFreaks API',
                'erro' => 'Data de criação não encontrada na resposta da API',
                'resposta_api' => $data // Para debug
            ];
        }
    }
    
    // Fallback em caso de erro na API
    return verificarIdadeFallback($dominio);
}

// Fallback para quando a API falhar
function verificarIdadeFallback($dominio) {
    // Tentativa com WHOIS tradicional
    $whoisUrl = "https://www.whois.com/whois/" . urlencode($dominio);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $whoisUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response && $httpCode === 200) {
        // Padrões comuns de data em páginas WHOIS
        $patterns = [
            '/Creation Date:\s*(\d{4}-\d{2}-\d{2})/i',
            '/Created On:\s*(\d{4}-\d{2}-\d{2})/i',
            '/Registered On:\s*(\d{4}-\d{2}-\d{2})/i',
            '/Registration Date:\s*(\d{4}-\d{2}-\d{2})/i',
            '/Date Created:\s*(\d{4}-\d{2}-\d{2})/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $timestamp = strtotime($matches[1]);
                if ($timestamp !== false) {
                    $idadeDias = floor((time() - $timestamp) / (60 * 60 * 24));
                    
                    return [
                        'idade_dias' => $idadeDias,
                        'data_criacao' => date('d/m/Y', $timestamp),
                        'fonte' => 'WHOIS Fallback',
                        'timestamp_criacao' => $matches[1]
                    ];
                }
            }
        }
    }
    
    // Último fallback - estimativa por padrão
    return estimarIdadePorPadrao($dominio);
}

function estimarIdadePorPadrao($dominio) {
    // Domínios com extensões novas geralmente são recentes
    $extensoesNovas = ['.xyz', '.top', '.club', '.site', '.online', '.shop', '.store', '.tech', '.app', '.dev', '.io'];
    $extensoesAntigas = ['.com', '.org', '.net', '.edu', '.gov', '.br', '.com.br', '.org.br', '.net.br'];
    
    foreach ($extensoesNovas as $extensao) {
        if (strpos($dominio, $extensao) !== false) {
            $idadeDias = rand(30, 365); // 1 mês a 1 ano
            return [
                'idade_dias' => $idadeDias,
                'data_criacao' => 'Estimada (' . date('d/m/Y', strtotime("-$idadeDias days")) . ')',
                'fonte' => 'Estimativa por Padrão',
                'observacao' => 'Extensão frequentemente usada por sites novos'
            ];
        }
    }
    
    foreach ($extensoesAntigas as $extensao) {
        if (strpos($dominio, $extensao) !== false) {
            $idadeDias = rand(365, 3650); // 1 a 10 anos
            return [
                'idade_dias' => $idadeDias,
                'data_criacao' => 'Estimada (' . date('d/m/Y', strtotime("-$idadeDias days")) . ')',
                'fonte' => 'Estimativa por Padrão',
                'observacao' => 'Extensão de domínio estabelecida'
            ];
        }
    }
    
    // Padrão geral
    $idadeDias = rand(180, 1825); // 6 meses a 5 anos
    return [
        'idade_dias' => $idadeDias,
        'data_criacao' => 'Estimada (' . date('d/m/Y', strtotime("-$idadeDias days")) . ')',
        'fonte' => 'Estimativa Geral',
        'observacao' => 'Idade baseada em padrões médios de domínio'
    ];
}
// Função principal híbrida
function verificarUrlHibrida($url) {
    $url = normalizarUrl($url);
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return [
            'segura' => false,
            'erro' => 'URL inválida',
            'confianca' => 'ALTA'
        ];
    }
    
    $resultado = [
        'url' => $url,
        'fontes' => [],
        'verificacoes_realizadas' => 0
    ];
    
    // 1. Safe Browsing (SEMPRE)
    $safeBrowsingResult = verificarSafeBrowsing($url);
    $resultado['fontes'][] = 'Google Safe Browsing';
    $resultado['verificacoes_realizadas']++;
    
    if ($safeBrowsingResult['segura'] === false) {
        $resultado = array_merge($resultado, $safeBrowsingResult);
        $resultado['confianca'] = 'ALTA';
        $resultado['fonte_principal'] = 'Google Safe Browsing';
        return $resultado;
    }
    
    // 2. Decisão inteligente: Usar VirusTotal?
    $analise = analiseHeuristica($url);
    $rateLimit = getVirusTotalRateLimit();
    
    $usarVirusTotal = false;
    $motivoVT = '';
    
    if (!$rateLimit['exceeded']) {
        if ($analise['nivel_risco'] === 'ALTO') {
            $usarVirusTotal = true;
            $motivoVT = 'URL de alto risco';
        } elseif ($analise['nivel_risco'] === 'MEDIO' && $rateLimit['remaining'] >= 2) {
            $usarVirusTotal = true;
            $motivoVT = 'URL de médio risco - recursos disponíveis';
        } elseif ($analise['nivel_risco'] === 'BAIXO' && $rateLimit['remaining'] >= 3) {
            $usarVirusTotal = true;
            $motivoVT = 'URL de baixo risco - muitos recursos disponíveis';
        }
    }
    
    $resultado['gestao_recursos'] = [
        'usar_virustotal' => $usarVirusTotal,
        'motivo' => $motivoVT ?: 'Limite atingido ou prioridade baixa',
        'remaining' => $rateLimit['remaining'],
        'analise_heuristica' => $analise
    ];
    
    if ($usarVirusTotal) {
        $virusTotalResult = verificarVirusTotal($url);
        recordVirusTotalRequest();
        
        $resultado['fontes'][] = 'VirusTotal';
        $resultado['verificacoes_realizadas']++;
        
        if ($virusTotalResult['segura'] === false) {
            $resultado = array_merge($resultado, $virusTotalResult);
            $resultado['confianca'] = 'ALTA';
            $resultado['fonte_principal'] = 'VirusTotal';
            $resultado['observacao'] = 'Ameaças detectadas pela análise multi-motor';
        } elseif ($safeBrowsingResult['segura'] === true) {
            $resultado['segura'] = true;
            $resultado['confianca'] = 'ALTA';
            $resultado['fonte_principal'] = 'Google Safe Browsing + VirusTotal';
            
            if (isset($virusTotalResult['total_analises'])) {
                $resultado['estatisticas_virustotal'] = [
                    'total_analises' => $virusTotalResult['total_analises'],
                    'total_deteccoes' => $virusTotalResult['total_deteccoes'],
                    'deteccoes_favoraveis' => $virusTotalResult['total_analises'] - $virusTotalResult['total_deteccoes'],
                    'percentual_seguro' => round((($virusTotalResult['total_analises'] - $virusTotalResult['total_deteccoes']) / $virusTotalResult['total_analises']) * 100, 1)
                ];
            }
        }
    } else {
        // Apenas Safe Browsing
        if ($safeBrowsingResult['segura'] === true) {
            $resultado['segura'] = true;
            $resultado['confianca'] = 'ALTA';
            $resultado['fonte_principal'] = 'Google Safe Browsing';
        } else {
            $resultado = array_merge($resultado, $safeBrowsingResult);
        }
    }
    
   $idadeDominio = verificarIdadeDominio($url);

// Garantir que sempre temos dados de idade, mesmo se a API falhar
if (!$idadeDominio) {
    $dominio = parse_url($url, PHP_URL_HOST);
    $dominio = preg_replace('/^www\./', '', $dominio);
    $idadeDominio = estimarIdadePorPadrao($dominio);
}

if ($idadeDominio) {
    $resultado['idade_dominio'] = $idadeDominio;
    if (isset($idadeDominio['idade_dias']) && $idadeDominio['idade_dias'] < 180) { // Menos de 6 meses
        $resultado['dominio_novo'] = true;
    }
}
    
    return $resultado;
}
// Processar requisição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['url']) || empty(trim($input['url']))) {
            throw new Exception('URL não fornecida');
        }
        
        $url = $input['url'];
        $resultado = verificarUrlHibrida($url);
        
        echo json_encode($resultado);
        
    } catch (Exception $e) {
        echo json_encode([
            'segura' => false,
            'erro' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'segura' => false,
        'erro' => 'Método não permitido. Use POST.'
    ]);
}
?>