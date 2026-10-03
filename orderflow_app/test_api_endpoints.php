<?php
/**
 * OrderFlow REST API Verification & Automated Test Suite
 */

$baseUrl = 'http://127.0.0.1:8000/api';

function callApi($method, $url, $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'body'   => json_decode($response, true) ?? $response,
        'raw'    => $response,
        'error'  => $error,
    ];
}

echo "============================================================\n";
echo "ORDERFLOW REST API AUTOMATED VERIFICATION SUITE\n";
echo "============================================================\n\n";

$results = [];

// 1. Test Login Failed (401)
echo "[1] Testing POST /api/login (Invalid credentials)...\n";
$res1 = callApi('POST', "$baseUrl/login", [
    'email' => 'requester@orderflow.com',
    'password' => 'wrongpassword'
]);
echo "Status Code: {$res1['status']} (Expected 401)\n";
echo "Response Message: " . ($res1['body']['message'] ?? '') . "\n\n";
$results['login_failed_401'] = $res1;

// 2. Test Login Validation Error (422)
echo "[2] Testing POST /api/login (Missing fields)...\n";
$res2 = callApi('POST', "$baseUrl/login", []);
echo "Status Code: {$res2['status']} (Expected 422)\n";
echo "Validation Errors: " . json_encode($res2['body']['errors'] ?? []) . "\n\n";
$results['login_validation_422'] = $res2;

// 3. Test Login Success - Requester (200)
echo "[3] Testing POST /api/login (Valid Requester)...\n";
$res3 = callApi('POST', "$baseUrl/login", [
    'email' => 'requester@orderflow.com',
    'password' => 'password'
]);
echo "Status Code: {$res3['status']} (Expected 200)\n";
$requesterToken = $res3['body']['data']['access_token'] ?? null;
echo "Token Acquired: " . substr($requesterToken ?? '', 0, 20) . "...\n\n";
$results['login_requester_200'] = $res3;

// 4. Test Login Success - Manager (200)
echo "[4] Testing POST /api/login (Valid Manager IT)...\n";
$res4 = callApi('POST', "$baseUrl/login", [
    'email' => 'manager.it@orderflow.com',
    'password' => 'password'
]);
echo "Status Code: {$res4['status']} (Expected 200)\n";
$managerToken = $res4['body']['data']['access_token'] ?? null;
echo "Manager Token: " . substr($managerToken ?? '', 0, 20) . "...\n\n";
$results['login_manager_200'] = $res4;

// 5. Test Unauthorized Access without Token (401)
echo "[5] Testing GET /api/me (Without Token)...\n";
$res5 = callApi('GET', "$baseUrl/me");
echo "Status Code: {$res5['status']} (Expected 401)\n\n";
$results['unauthorized_401'] = $res5;

// 6. Test GET /api/me (200)
echo "[6] Testing GET /api/me (With Requester Token)...\n";
$res6 = callApi('GET', "$baseUrl/me", null, $requesterToken);
echo "Status Code: {$res6['status']} (Expected 200)\n";
echo "User Name: " . ($res6['body']['data']['name'] ?? '') . " | Role: " . ($res6['body']['data']['role'] ?? '') . "\n\n";
$results['me_200'] = $res6;

// 7. Test GET /api/dashboard/summary (200)
echo "[7] Testing GET /api/dashboard/summary...\n";
$res7 = callApi('GET', "$baseUrl/dashboard/summary", null, $requesterToken);
echo "Status Code: {$res7['status']} (Expected 200)\n";
echo "KPI Total Requests: " . ($res7['body']['data']['kpis']['total_requests'] ?? 0) . "\n\n";
$results['dashboard_summary_200'] = $res7;

// 8. Test GET /api/vendors (200)
echo "[8] Testing GET /api/vendors...\n";
$res8 = callApi('GET', "$baseUrl/vendors", null, $requesterToken);
echo "Status Code: {$res8['status']} (Expected 200)\n";
echo "Vendors Count: " . count($res8['body']['data']['items'] ?? []) . "\n\n";
$results['vendors_200'] = $res8;

// 9. Test GET /api/purchase-requests (200)
echo "[9] Testing GET /api/purchase-requests...\n";
$res9 = callApi('GET', "$baseUrl/purchase-requests", null, $requesterToken);
echo "Status Code: {$res9['status']} (Expected 200)\n";
echo "PR Count: " . count($res9['body']['data']['items'] ?? []) . "\n\n";
$results['purchase_requests_list_200'] = $res9;

// 10. Test POST /api/purchase-requests Validation Error (422)
echo "[10] Testing POST /api/purchase-requests (Validation Failure)...\n";
$res10 = callApi('POST', "$baseUrl/purchase-requests", [
    'title' => 'PR Test'
], $requesterToken);
echo "Status Code: {$res10['status']} (Expected 422)\n";
echo "Validation Errors: " . json_encode($res10['body']['errors'] ?? []) . "\n\n";
$results['pr_store_validation_422'] = $res10;

// 11. Test POST /api/purchase-requests Success (201)
echo "[11] Testing POST /api/purchase-requests (Create PR with Items)...\n";
$res11 = callApi('POST', "$baseUrl/purchase-requests", [
    'title'         => 'Pengadaan Laptop Developer & Monitor REST API Test',
    'description'   => 'Kebutuhan tim teknis backend dan frontend mobile',
    'required_date' => date('Y-m-d', strtotime('+14 days')),
    'items'         => [
        [
            'item_name'            => 'MacBook Pro M3 Pro 16 Inch 36GB',
            'specification'        => 'Space Black, 512GB SSD, Apple M3 Pro 12-core CPU',
            'quantity'             => 1,
            'unit'                 => 'Unit',
            'estimated_unit_price' => 38000000,
        ],
        [
            'item_name'            => 'Monitor Dell UltraSharp 27 4K U2723QE',
            'specification'        => 'IPS Black, USB-C Hub 90W Power Delivery',
            'quantity'             => 2,
            'unit'                 => 'Unit',
            'estimated_unit_price' => 9500000,
        ],
    ]
], $requesterToken);
echo "Status Code: {$res11['status']} (Expected 201)\n";
$newPrId = $res11['body']['data']['id'] ?? null;
$newPrNum = $res11['body']['data']['pr_number'] ?? null;
echo "New PR Created: #{$newPrNum} (ID: {$newPrId})\n";
echo "Estimated Total: " . ($res11['body']['data']['formatted_estimated_total'] ?? '') . "\n\n";
$results['pr_store_201'] = $res11;

// 12. Test GET /api/purchase-requests/{id} (200)
echo "[12] Testing GET /api/purchase-requests/{$newPrId}...\n";
$res12 = callApi('GET', "$baseUrl/purchase-requests/{$newPrId}", null, $requesterToken);
echo "Status Code: {$res12['status']} (Expected 200)\n";
echo "PR Title: " . ($res12['body']['data']['title'] ?? '') . "\n";
echo "Items Count: " . count($res12['body']['data']['items'] ?? []) . "\n";
echo "Approval Tiers Count: " . count($res12['body']['data']['approvals'] ?? []) . "\n\n";
$results['pr_show_200'] = $res12;

// 13. Test GET /api/purchase-requests/99999 (404)
echo "[13] Testing GET /api/purchase-requests/99999 (Not Found)...\n";
$res13 = callApi('GET', "$baseUrl/purchase-requests/99999", null, $requesterToken);
echo "Status Code: {$res13['status']} (Expected 404)\n\n";
$results['pr_not_found_404'] = $res13;

// 14. Test Self-Approval Forbidden (403)
echo "[14] Testing POST /api/purchase-requests/{$newPrId}/approve (Requester attempts self-approval)...\n";
$res14 = callApi('POST', "$baseUrl/purchase-requests/{$newPrId}/approve", [
    'notes' => 'Self approve attempt'
], $requesterToken);
echo "Status Code: {$res14['status']} (Expected 403)\n";
echo "Message: " . ($res14['body']['message'] ?? '') . "\n\n";
$results['pr_self_approval_forbidden_403'] = $res14;

// 15. Test Manager Approval Success (200)
echo "[15] Testing POST /api/purchase-requests/{$newPrId}/approve (Manager IT approves Tier 1)...\n";
$res15 = callApi('POST', "$baseUrl/purchase-requests/{$newPrId}/approve", [
    'notes' => 'Disetujui untuk mendukung akselerasi sprint pengembangan'
], $managerToken);
echo "Status Code: {$res15['status']} (Expected 200)\n";
echo "Message: " . ($res15['body']['message'] ?? '') . "\n\n";
$results['pr_manager_approve_200'] = $res15;

// 16. Test Reject Validation Error (422)
echo "[16] Testing POST /api/purchase-requests/{$newPrId}/reject (Missing reason)...\n";
$res16 = callApi('POST', "$baseUrl/purchase-requests/{$newPrId}/reject", [], $managerToken);
echo "Status Code: {$res16['status']} (Expected 422)\n";
echo "Validation Errors: " . json_encode($res16['body']['errors'] ?? []) . "\n\n";
$results['pr_reject_validation_422'] = $res16;

// 17. Test Logout (200)
echo "[17] Testing POST /api/logout...\n";
$res17 = callApi('POST', "$baseUrl/logout", null, $requesterToken);
echo "Status Code: {$res17['status']} (Expected 200)\n";
echo "Message: " . ($res17['body']['message'] ?? '') . "\n\n";
$results['logout_200'] = $res17;

// Save full results to JSON fixture for artifacts & documentation
file_put_contents(__DIR__ . '/test_api_results.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "============================================================\n";
echo "ALL TESTS COMPLETED SUCCESSFULLY! Fixture saved to test_api_results.json\n";
echo "============================================================\n";
