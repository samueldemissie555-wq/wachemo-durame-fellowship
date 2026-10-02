<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/admin.php';
require_role(['super_admin','admin']);

$pdo = db();
$types = ['text','email','tel','number','date','textarea','select','file'];

function normalize_form_fields(string $json, array $types): array {
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        throw new Exception('Invalid JSON. Please use valid JSON or the visual field builder.');
    }

    $clean = [];
    foreach ($decoded as $index => $f) {
        if (!is_array($f)) {
            continue;
        }

        $name = preg_replace(
            '/[^a-z0-9_]/',
            '_',
            strtolower(trim((string)($f['name'] ?? '')))
        );
        $name = trim($name, '_');

        $type = (string)($f['type'] ?? 'text');
        $label = trim((string)($f['label'] ?? ''));

        if ($name === '' || !in_array($type, $types, true)) {
            continue;
        }

        if ($label === '') {
            $label = ucwords(str_replace(['_', '-'], ' ', $name));
        }

        $options = [];
        if ($type === 'select') {
            $source = $f['options'] ?? [];
            if (is_string($source)) {
                $source = preg_split('/\r\n|\r|\n/', $source);
            }
            if (!is_array($source)) {
                $source = [];
            }
            foreach ($source as $option) {
                $option = trim((string)$option);
                if ($option !== '') {
                    $options[] = $option;
                }
            }
        }

        $clean[] = [
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'required' => !empty($f['required']),
            'options' => array_values($options)
        ];
    }

    if (!$clean) {
        throw new Exception('Add at least one valid field.');
    }

    $names = [];
    foreach ($clean as $f) {
        if (isset($names[$f['name']])) {
            throw new Exception('Duplicate field name: '.$f['name']);
        }
        $names[$f['name']] = true;

        if ($f['type'] === 'select' && count($f['options']) === 0) {
            throw new Exception('The select field "'.$f['label'].'" needs at least one option.');
        }
    }

    return $clean;
}

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim((string)($_POST['title'] ?? ''));
            $title_am = trim((string)($_POST['title_am'] ?? ''));
            $desc = trim((string)($_POST['description'] ?? ''));
            $desc_am = trim((string)($_POST['description_am'] ?? ''));
            $slug = strtolower(trim((string)($_POST['slug'] ?? '')));
            $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
            $slug = trim($slug, '-');

            if ($slug === '') {
                $slug = 'form-'.time();
            }

            $status = in_array(
                $_POST['status'] ?? '',
                ['draft','published','archived'],
                true
            ) ? $_POST['status'] : 'draft';

            if ($title === '') {
                throw new Exception('Enter a form title.');
            }

            $clean = normalize_form_fields(
                (string)($_POST['fields_json'] ?? ''),
                $types
            );

            // Give a clear message instead of a raw MySQL duplicate-key error.
            $check = $pdo->prepare(
                'SELECT id FROM custom_forms WHERE slug=? AND id<>? LIMIT 1'
            );
            $check->execute([$slug, $id]);
            if ($check->fetch()) {
                throw new Exception('This slug is already used. Choose another slug.');
            }

            $json = json_encode(
                $clean,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            if ($id) {
                $st = $pdo->prepare(
                    'UPDATE custom_forms
                     SET title=?,title_am=?,description=?,description_am=?,slug=?,fields=?,status=?
                     WHERE id=?'
                );
                $st->execute([
                    $title, $title_am, $desc, $desc_am,
                    $slug, $json, $status, $id
                ]);
                log_admin("Updated custom form #$id");
            } else {
                $st = $pdo->prepare(
                    'INSERT INTO custom_forms
                     (title,title_am,description,description_am,slug,fields,status,created_by)
                     VALUES(?,?,?,?,?,?,?,?)'
                );
                $st->execute([
                    $title, $title_am, $desc, $desc_am,
                    $slug, $json, $status, $_SESSION['admin_id'] ?? null
                ]);
                log_admin('Created custom form');
            }

            flash('admin_success', 'Form saved successfully.');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare('DELETE FROM custom_forms WHERE id=?')->execute([$id]);
                log_admin("Deleted custom form #$id");
            }
            flash('admin_success', 'Form deleted.');
        }
    } catch (Throwable $e) {
        flash('admin_error', 'Form error: '.$e->getMessage());
    }

    redirect('admin/forms.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM custom_forms WHERE id=?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}

$rows = $pdo->query(
    'SELECT f.*,
        (SELECT COUNT(*) FROM custom_form_submissions s WHERE s.form_id=f.id) submission_count
     FROM custom_forms f
     ORDER BY f.created_at DESC'
)->fetchAll();

$defaultFields = [
    [
        'name' => 'full_name',
        'label' => 'Full Name',
        'type' => 'text',
        'required' => true,
        'options' => []
    ],
    [
        'name' => 'phone',
        'label' => 'Phone Number',
        'type' => 'tel',
        'required' => true,
        'options' => []
    ],
    [
        'name' => 'department',
        'label' => 'Department',
        'type' => 'text',
        'required' => false,
        'options' => []
    ],
    [
        'name' => 'message',
        'label' => 'Message',
        'type' => 'textarea',
        'required' => true,
        'options' => []
    ]
];

$editFields = $defaultFields;
if ($edit && !empty($edit['fields'])) {
    $decoded = json_decode((string)$edit['fields'], true);
    if (is_array($decoded) && $decoded) {
        $editFields = $decoded;
    }
}

$initialJson = json_encode(
    $editFields,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
);

admin_page(
    'Form Builder',
    'forms',
    'Create registration, application, survey, prayer, event or other custom forms without changing PHP code.'
);
admin_flash();
?>

<div class="admin-panel form-builder-intro">
    <h2>Admin Form Builder</h2>
    <p>
        Build your form visually. You no longer need to type JSON manually.
        Add fields, choose their type, then save the form.
    </p>

    <div class="builder-tip">
        <b>How it works</b>
        <ol class="builder-help-list">
            <li>Enter the form title and slug.</li>
            <li>Click <strong>Add Field</strong> and configure each field.</li>
            <li>For a dropdown, add one option per line.</li>
            <li>Save the form. The system automatically creates the correct JSON.</li>
        </ol>
    </div>
</div>

<div class="admin-panel">
    <h2><?= $edit ? 'Edit Form' : 'Create New Form' ?></h2>

    <form
        class="admin-form"
        id="formBuilderForm"
        method="post"
        novalidate
    >
        <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?=e((string)($edit['id'] ?? 0))?>">

        <div class="admin-form-grid">
            <label>
                Title
                <input
                    name="title"
                    required
                    value="<?=e($edit['title'] ?? '')?>"
                    placeholder="Student Application Form"
                >
            </label>

            <label>
                Slug
                <input
                    name="slug"
                    id="formSlug"
                    required
                    value="<?=e($edit['slug'] ?? '')?>"
                    placeholder="student-application"
                    pattern="[a-z0-9-]+"
                >
                <small>Use lowercase letters, numbers and hyphens only.</small>
            </label>
        </div>

        <div class="admin-form-grid">
            <label>
                Title (Amharic)
                <input name="title_am" value="<?=e($edit['title_am'] ?? '')?>">
            </label>

            <label>
                Status
                <select name="status">
                    <?php foreach(['draft','published','archived'] as $s): ?>
                        <option
                            value="<?=$s?>"
                            <?=($edit['status'] ?? 'draft') === $s ? 'selected' : ''?>
                        ><?=$s?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="admin-form-grid">
            <label>
                Description
                <textarea name="description" rows="3"><?=e($edit['description'] ?? '')?></textarea>
            </label>

            <label>
                Description (Amharic)
                <textarea name="description_am" rows="3"><?=e($edit['description_am'] ?? '')?></textarea>
            </label>
        </div>

        <div class="builder-section-head">
            <div>
                <h3>Form Fields</h3>
                <p class="muted">Create fields without writing JSON.</p>
            </div>
            <button type="button" class="btn primary" id="addFieldBtn">＋ Add Field</button>
        </div>

        <div id="fieldBuilder" class="field-builder"></div>

        <div class="builder-empty" id="builderEmpty" hidden>
            No fields yet. Click <strong>Add Field</strong>.
        </div>

        <div class="json-tools">
            <div class="builder-section-head compact">
                <div>
                    <h3>Advanced JSON</h3>
                    <p class="muted">
                        Optional. Use this only if you want to edit/import JSON directly.
                    </p>
                </div>
                <div class="builder-json-actions">
                    <button type="button" class="btn small outline" id="validateJsonBtn">✓ Validate JSON</button>
                    <button type="button" class="btn small outline" id="applyJsonBtn">↙ Apply JSON to Fields</button>
                    <button type="button" class="btn small outline" id="copyJsonBtn">Copy JSON</button>
                </div>
            </div>

            <textarea
                name="fields_json"
                id="fieldsJson"
                rows="16"
                spellcheck="false"
                aria-label="Form fields JSON"
            ><?=e($initialJson)?></textarea>

            <div id="jsonStatus" class="json-status" role="status"></div>
        </div>

        <div class="admin-actions">
            <button class="btn primary" type="submit">Save Form</button>
            <?php if($edit): ?>
                <a class="btn outline" href="forms.php">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="admin-panel">
    <h2>Forms</h2>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <tr>
                <th>Form</th>
                <th>Status</th>
                <th>Submissions</th>
                <th>Public Link</th>
                <th>Actions</th>
            </tr>

            <?php foreach($rows as $r): ?>
                <tr>
                    <td>
                        <strong><?=e($r['title'])?></strong><br>
                        <small><?=e($r['slug'])?></small>
                    </td>
                    <td>
                        <span class="status <?=e($r['status'])?>">
                            <?=e($r['status'])?>
                        </span>
                    </td>
                    <td><?=e((string)$r['submission_count'])?></td>
                    <td>
                        <?php if($r['status'] === 'published'): ?>
                            <a
                                class="text-link"
                                target="_blank"
                                rel="noopener"
                                href="../form.php?slug=<?=e(rawurlencode($r['slug']))?>"
                            >Open form ↗</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="btn small outline" href="forms.php?edit=<?=$r['id']?>">Edit</a>
                        <a class="btn small outline" href="form-submissions.php?id=<?=$r['id']?>">Submissions</a>

                        <form
                            style="display:inline"
                            method="post"
                            onsubmit="return confirm('Delete this form and its submissions?')"
                        >
                            <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?=$r['id']?>">
                            <button class="btn small danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="5" class="muted">No custom forms created yet.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
(function () {
    'use strict';

    const initialFields = <?=json_encode($editFields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)?>;
    const builder = document.getElementById('fieldBuilder');
    const empty = document.getElementById('builderEmpty');
    const jsonBox = document.getElementById('fieldsJson');
    const form = document.getElementById('formBuilderForm');
    const addBtn = document.getElementById('addFieldBtn');
    const validateBtn = document.getElementById('validateJsonBtn');
    const applyBtn = document.getElementById('applyJsonBtn');
    const copyBtn = document.getElementById('copyJsonBtn');
    const status = document.getElementById('jsonStatus');
    const slug = document.getElementById('formSlug');

    const typeNames = {
        text: 'Text',
        email: 'Email',
        tel: 'Phone',
        number: 'Number',
        date: 'Date',
        textarea: 'Long Text',
        select: 'Dropdown',
        file: 'File Upload'
    };

    function slugify(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function safeField(field) {
        field = field || {};
        return {
            name: String(field.name || ''),
            label: String(field.label || ''),
            type: typeNames[field.type] ? field.type : 'text',
            required: !!field.required,
            options: Array.isArray(field.options) ? field.options.map(String) : []
        };
    }

    function currentFields() {
        return Array.from(builder.querySelectorAll('.field-card')).map(function (card) {
            const options = card.querySelector('.field-options');
            return {
                name: card.querySelector('.field-name').value.trim(),
                label: card.querySelector('.field-label').value.trim(),
                type: card.querySelector('.field-type').value,
                required: card.querySelector('.field-required').checked,
                options: options
                    ? options.value.split(/\r?\n/).map(v => v.trim()).filter(Boolean)
                    : []
            };
        });
    }

    function updateJson() {
        const fields = currentFields();
        jsonBox.value = JSON.stringify(fields, null, 2);
        return fields;
    }

    function setStatus(message, good) {
        status.textContent = message || '';
        status.className = 'json-status ' + (good ? 'good' : 'bad');
    }

    function createCard(field, index) {
        field = safeField(field);

        const card = document.createElement('div');
        card.className = 'field-card';
        card.innerHTML = `
            <div class="field-card-top">
                <div class="field-number">${index + 1}</div>
                <div class="field-card-title">
                    <strong>Field <span class="field-title-name"></span></strong>
                    <small class="field-summary"></small>
                </div>
                <div class="field-card-actions">
                    <button type="button" class="btn small outline move-up" title="Move up">↑</button>
                    <button type="button" class="btn small outline move-down" title="Move down">↓</button>
                    <button type="button" class="btn small danger remove-field">Remove</button>
                </div>
            </div>

            <div class="admin-form-grid">
                <label>
                    Field Name
                    <input class="field-name" placeholder="full_name" autocomplete="off">
                    <small>Use letters, numbers and underscore.</small>
                </label>

                <label>
                    Label
                    <input class="field-label" placeholder="Full Name">
                </label>
            </div>

            <div class="admin-form-grid field-config-grid">
                <label>
                    Field Type
                    <select class="field-type">
                        ${Object.keys(typeNames).map(function (key) {
                            return `<option value="${key}">${typeNames[key]}</option>`;
                        }).join('')}
                    </select>
                </label>

                <label class="check-label">
                    <input class="field-required" type="checkbox">
                    <span>Required field</span>
                </label>
            </div>

            <label class="options-wrap" hidden>
                Dropdown Options
                <textarea class="field-options" rows="4" placeholder="3rd Year&#10;4th Year&#10;5th Year"></textarea>
                <small>Enter one option per line.</small>
            </label>
        `;

        card.querySelector('.field-name').value = field.name;
        card.querySelector('.field-label').value = field.label;
        card.querySelector('.field-type').value = field.type;
        card.querySelector('.field-required').checked = field.required;

        const optionsWrap = card.querySelector('.options-wrap');
        const options = card.querySelector('.field-options');
        options.value = field.options.join('\n');

        function refresh() {
            const type = card.querySelector('.field-type').value;
            optionsWrap.hidden = type !== 'select';
            card.querySelector('.field-title-name').textContent =
                card.querySelector('.field-label').value.trim() ||
                card.querySelector('.field-name').value.trim() ||
                'New Field';

            const summary = [
                typeNames[type],
                card.querySelector('.field-required').checked ? 'Required' : 'Optional'
            ];
            card.querySelector('.field-summary').textContent = summary.join(' • ');
            renumber();
            updateJson();
        }

        card.querySelectorAll('input, textarea, select').forEach(function (el) {
            el.addEventListener('input', refresh);
            el.addEventListener('change', refresh);
        });

        card.querySelector('.remove-field').addEventListener('click', function () {
            card.remove();
            renumber();
            updateJson();
            toggleEmpty();
        });

        card.querySelector('.move-up').addEventListener('click', function () {
            const previous = card.previousElementSibling;
            if (previous) {
                builder.insertBefore(card, previous);
                renumber();
                updateJson();
            }
        });

        card.querySelector('.move-down').addEventListener('click', function () {
            const next = card.nextElementSibling;
            if (next) {
                builder.insertBefore(next, card);
                renumber();
                updateJson();
            }
        });

        builder.appendChild(card);
        refresh();
        return card;
    }

    function renumber() {
        Array.from(builder.querySelectorAll('.field-card')).forEach(function (card, i) {
            card.querySelector('.field-number').textContent = i + 1;
        });
    }

    function toggleEmpty() {
        empty.hidden = builder.querySelectorAll('.field-card').length > 0;
    }

    function loadFields(fields) {
        builder.innerHTML = '';
        if (!Array.isArray(fields)) {
            throw new Error('JSON must contain an array of fields.');
        }

        fields.forEach(function (field, index) {
            createCard(field, index);
        });

        toggleEmpty();
        renumber();
        updateJson();
    }

    addBtn.addEventListener('click', function () {
        createCard({
            name: 'field_' + (builder.querySelectorAll('.field-card').length + 1),
            label: 'New Field',
            type: 'text',
            required: false,
            options: []
        }, builder.querySelectorAll('.field-card').length);

        toggleEmpty();

        const cards = builder.querySelectorAll('.field-card');
        const last = cards[cards.length - 1];
        if (last) {
            last.querySelector('.field-name').focus();
        }
    });

    validateBtn.addEventListener('click', function () {
        try {
            const data = JSON.parse(jsonBox.value);
            if (!Array.isArray(data)) {
                throw new Error('Top-level JSON must be an array.');
            }

            data.forEach(function (field, i) {
                if (!field || typeof field !== 'object') {
                    throw new Error('Field #' + (i + 1) + ' must be an object.');
                }
                if (!field.name) {
                    throw new Error('Field #' + (i + 1) + ' is missing "name".');
                }
                if (!typeNames[field.type]) {
                    throw new Error('Field "' + field.name + '" has an invalid type.');
                }
            });

            setStatus('✓ JSON is valid and ready to apply.', true);
        } catch (err) {
            setStatus('✕ ' + err.message, false);
        }
    });

    applyBtn.addEventListener('click', function () {
        try {
            const data = JSON.parse(jsonBox.value);
            loadFields(data);
            setStatus('✓ JSON applied to the visual field builder.', true);
        } catch (err) {
            setStatus('✕ ' + err.message, false);
        }
    });

    copyBtn.addEventListener('click', async function () {
        try {
            await navigator.clipboard.writeText(jsonBox.value);
            setStatus('✓ JSON copied.', true);
        } catch (err) {
            jsonBox.select();
            document.execCommand('copy');
            setStatus('✓ JSON copied.', true);
        }
    });

    form.addEventListener('submit', function (event) {
        const fields = currentFields();

        if (!fields.length) {
            event.preventDefault();
            setStatus('✕ Add at least one field.', false);
            empty.hidden = false;
            return;
        }

        const seen = {};
        for (let i = 0; i < fields.length; i++) {
            const f = fields[i];

            f.name = f.name
                .toLowerCase()
                .replace(/[^a-z0-9_]/g, '_')
                .replace(/^_+|_+$/g, '');

            if (!f.name) {
                event.preventDefault();
                setStatus('✕ Field #' + (i + 1) + ' needs a valid field name.', false);
                return;
            }

            if (seen[f.name]) {
                event.preventDefault();
                setStatus('✕ Duplicate field name: ' + f.name, false);
                return;
            }
            seen[f.name] = true;

            if (!f.label) {
                f.label = f.name
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, c => c.toUpperCase());
            }

            if (f.type === 'select' && !f.options.length) {
                event.preventDefault();
                setStatus('✕ Dropdown "' + f.label + '" needs at least one option.', false);
                return;
            }
        }

        jsonBox.value = JSON.stringify(fields, null, 2);

        if (!slug.value.trim()) {
            event.preventDefault();
            setStatus('✕ Enter a form slug.', false);
            slug.focus();
            return;
        }

        slug.value = slugify(slug.value);
    });

    // Auto-create slug from title only when slug is empty.
    document.querySelector('input[name="title"]').addEventListener('input', function () {
        if (!slug.dataset.edited) {
            slug.value = slugify(this.value);
        }
    });

    slug.addEventListener('input', function () {
        this.dataset.edited = '1';
        this.value = slugify(this.value);
    });

    loadFields(initialFields);
})();
</script>

<?php admin_end(); ?>
