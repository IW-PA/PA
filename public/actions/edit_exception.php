<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once SRC_PATH . '/services/ActivityLogger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('exceptions.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Token de sécurité invalide.');
    redirect('exceptions.php');
}

$str = static function ($key) {
    return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
};

$exception_id = intval($_POST['exception_id'] ?? 0);
$target       = $str('target');
$name         = $str('name');
$description  = $str('description');
$amount       = $str('amount');
// Durée: "Ponctuelle" or "Tous les N mois" with an arbitrary N. interval_months is
// the source of truth; frequency keeps a valid ENUM value for legacy readers.
$recurrence = is_string($_POST['recurrence'] ?? null) ? trim($_POST['recurrence']) : '';
$rawInterval = $_POST['interval_months'] ?? null;
$isRecurring = ($recurrence === 'recurrent');
$intervalMonths = null;
$frequency = 'ponctuel';
if ($isRecurring) {
    $frequency = 'recurrent';
    $intervalMonths = (is_scalar($rawInterval) && ctype_digit((string) $rawInterval)) ? (int) $rawInterval : 0;
}
$start_date   = $str('start_date');
$end_date     = $str('end_date');

$errors = [];

if ($exception_id <= 0) {
    setFlashMessage('error', 'Exception invalide.');
    redirect('exceptions.php');
}

$target_type = null;
$target_id   = 0;
if (preg_match('/^(expense|income):(\d+)$/', $target, $m)) {
    $target_type = $m[1];
    $target_id   = (int) $m[2];
} else {
    $errors[] = 'Veuillez sélectionner la dépense ou le revenu concerné.';
}

if ($name === '') {
    $errors[] = 'Le nom de l\'exception est requis.';
}

if (!is_numeric($amount) || (float) $amount <= 0) {
    $errors[] = 'Le montant doit être un nombre supérieur à 0.';
}

if ($recurrence !== 'ponctuel' && $recurrence !== 'recurrent') {
    $errors[] = 'Veuillez sélectionner une durée.';
} elseif ($isRecurring && ($intervalMonths < 1 || $intervalMonths > 120)) {
    $errors[] = 'Le nombre de mois doit être un entier compris entre 1 et 120.';
}

if ($start_date === '' || !strtotime($start_date)) {
    $errors[] = 'La date de début est requise et doit être valide.';
}

if ($end_date !== '' && !strtotime($end_date)) {
    $errors[] = 'Date de fin invalide.';
}

if ($start_date !== '' && $end_date !== '' && strtotime($start_date) > strtotime($end_date)) {
    $errors[] = 'La date de début doit être antérieure à la date de fin.';
}

if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    redirect('exceptions.php');
}

try {
    // The exception itself must belong to the logged-in user.
    $exception = fetchOne(
        "SELECT id FROM exceptions WHERE id = ? AND user_id = ?",
        [$exception_id, $_SESSION['user_id']]
    );

    if (!$exception) {
        setFlashMessage('error', 'Exception non trouvée ou accès non autorisé.');
        redirect('exceptions.php');
    }

    // ...and so must the dépense/revenu it is being attached to.
    $table  = $target_type === 'expense' ? 'expenses' : 'incomes';
    $parent = fetchOne(
        "SELECT id, name FROM {$table} WHERE id = ? AND user_id = ? AND deleted_at IS NULL",
        [$target_id, $_SESSION['user_id']]
    );

    if (!$parent) {
        setFlashMessage('error', 'Dépense ou revenu non trouvé ou accès non autorisé.');
        redirect('exceptions.php');
    }

    updateRecord('exceptions', [
        'expense_id'  => $target_type === 'expense' ? $target_id : null,
        'income_id'   => $target_type === 'income' ? $target_id : null,
        'name'        => $name,
        'description' => $description !== '' ? $description : null,
        'amount'      => $amount,
        'frequency'   => $frequency,
        'interval_months' => $intervalMonths,
        'start_date'  => $start_date,
        'end_date'    => $end_date !== '' ? $end_date : null,
    ], 'id = :id AND user_id = :user_id', ['id' => $exception_id, 'user_id' => $_SESSION['user_id']]);

    ActivityLogger::log(
        (int) $_SESSION['user_id'],
        'exception.update',
        'exception',
        $exception_id,
        ['name' => $name, 'target' => $target_type, 'target_name' => $parent['name']]
    );

    setFlashMessage('success', 'Exception mise à jour avec succès !');

} catch (Exception $e) {
    error_log("Edit exception error: " . $e->getMessage());
    setFlashMessage('error', 'Erreur lors de la modification de l\'exception.');
}

redirect('exceptions.php');
