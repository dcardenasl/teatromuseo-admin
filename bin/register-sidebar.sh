#!/usr/bin/env bash
# register-sidebar.sh — auto-register sidebar entries for template modules
set -euo pipefail

NC='\033*0m'
NC='\033[0m'
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'

if [ $# -lt 1 ]; then
    echo "Usage: bash bin/register-sidebar.sh <path/to/template.json>" >&2
    exit 1
fi

TEMPLATE_JSON="$1"
if [ ! -f "$TEMPLATE_JSON" ]; then
    echo -e "${RED}Error: File not found: ${TEMPLATE_JSON}${NC}" >&2
    exit 1
fi

echo -e "${BLUE}Registering sidebar entries from $(basename "$TEMPLATE_JSON")...${NC}"

# Python script to parse template.json and patch sidebar.php and Language files
python3 - << 'PYEOF' "$TEMPLATE_JSON"
import sys
import json
import re
import os

template_path = sys.argv[1]
with open(template_path, 'r') as f:
    data = json.load(f)

if 'admin_sidebar' not in data or not data['admin_sidebar']:
    print("No admin_sidebar configured in template.json - skipping.")
    sys.exit(0)

# Helpers to match make-module.sh derivations
def to_snake(s):
    # StudlyCase -> snake_case, treating runs of uppercase as one word
    s = re.sub(r"([a-z0-9])([A-Z])", r"\1_\2", s)
    s = re.sub(r"([A-Z]+)([A-Z][a-z])", r"\1_\2", s)
    return s.lower()

def pluralize(w):
    if re.search(r'[^aeiou]y$', w):
        return w[:-1] + "ies"
    elif re.search(r'(s|x|z|ch|sh)$', w):
        return w + "es"
    else:
        return w + "s"

sidebar_file = "app/Views/layouts/partials/sidebar.php"
if not os.path.exists(sidebar_file):
    print(f"Error: Sidebar file not found: {sidebar_file}")
    sys.exit(1)

with open(sidebar_file, 'r') as f:
    sidebar_content = f.read()

# Build mapping of resources for quick lookup
admin_modules = {m['resource']: m for m in data.get('admin_modules', [])}

def resolve_item(module, icon, item):
    """Resolve one admin_sidebar item name against admin_modules. Returns
    (route_name, route_pattern, lang_key, icon) or None if unresolvable."""
    m_info = admin_modules.get(item)
    if not m_info:
        print(f"Warning: Resource {item} listed in sidebar but not found in admin_modules.")
        return None

    route_seg = m_info.get('route_segment')
    if not route_seg:
        res_snake = to_snake(item)
        res_plural = pluralize(res_snake)
        route_seg = res_plural.replace('_', '-')

    route_seg_underscore = route_seg.replace('-', '_')
    route_module = m_info.get('module', module)
    route_module_lower = route_module.lower()

    route_name = f"admin.{route_module_lower}.{route_seg_underscore}"
    route_pattern = f"admin/{route_module_lower}/{route_seg}"
    res_snake = to_snake(item)
    res_plural = pluralize(res_snake)
    lang_key = f"{route_module}.{res_plural}_title"

    return route_name, route_pattern, lang_key, m_info.get('icon', icon)

def to_php_ident(s):
    # Safe fragment for a PHP variable name: lowercase alnum + underscore.
    return re.sub(r'[^a-z0-9_]', '_', s.lower())

def link_lines(indent, route_name, route_pattern, lang_key, icon, sub_item=False):
    if sub_item:
        cls = f"<?= $navSubItemClass ?> <?= active_nav('{route_pattern}*', $navSubItemActiveClass) ?> <?= url_is('{route_pattern}*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>"
    else:
        cls = f"flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-brand-50 hover:text-brand-700 <?= active_nav('{route_pattern}*') ?>"
    return [
        f"{indent}<a href=\"<?= route_to('{route_name}') ?>\" class=\"{cls}\">",
        f"{indent}    <?= ui_icon('{icon}') ?>",
        f"{indent}    <span><?= lang('{lang_key}') ?></span>",
        f"{indent}</a>",
    ]

for group in data['admin_sidebar']:
    module = group['module']
    label = group['label']
    icon = group['icon']
    permission = group['permission']

    # 1. Build sidebar HTML block
    block_lines = [
        f"        <!-- START {module} -->",
        f"        <?php if (has_permission('{permission}')): ?>",
        f"            <div class=\"pt-3 mt-3 border-t border-gray-800 text-xs uppercase text-gray-500\"><?= lang('{label}') ?></div>"
    ]

    if 'groups' in group and group['groups']:
        # Nested collapsible sub-groups — same Alpine/localStorage pattern as
        # the hand-written CMS groups in sidebar.php (open state persists,
        # auto-expands when a child route is active).
        for sub in group['groups']:
            gkey = sub['key']
            glabel = sub['label']
            resolved = [resolve_item(module, icon, item) for item in sub['items']]
            resolved = [r for r in resolved if r is not None]
            if not resolved:
                continue

            active_var = f"$sidebarActive_{to_php_ident(module)}_{to_php_ident(gkey)}"
            active_expr = " || ".join(f"url_is('{r[1]}*')" for r in resolved)
            storage_key = f"{to_php_ident(module)}-g-{to_php_ident(gkey)}"

            block_lines.append(f"            <?php {active_var} = {active_expr}; ?>")
            block_lines.append(f"            <div x-data=\"{{ open: <?= {active_var} ? 'true' : \"localStorage.getItem('{storage_key}') !== 'false'\" ?> }}\" class=\"space-y-1\">")
            block_lines.append(f"                <button type=\"button\"")
            block_lines.append(f"                        @click=\"open = !open; localStorage.setItem('{storage_key}', open)\"")
            block_lines.append(f"                        class=\"<?= $navGroupButtonClass ?>\"")
            block_lines.append(f"                        :aria-expanded=\"open\">")
            block_lines.append(f"                    <span class=\"text-xs font-medium uppercase tracking-wide text-gray-500\"><?= lang('{glabel}') ?></span>")
            block_lines.append(f"                    <span class=\"inline-flex items-center justify-center transition-transform duration-200\" :class=\"{{ 'rotate-180': open }}\">")
            block_lines.append(f"                        <?= ui_icon('chevron-down', 'h-3 w-3 text-gray-500') ?>")
            block_lines.append(f"                    </span>")
            block_lines.append(f"                </button>")
            block_lines.append(f"                <div x-show=\"open\" x-cloak class=\"<?= $navGroupBodyClass ?>\">")
            for route_name, route_pattern, lang_key, item_icon in resolved:
                block_lines.extend(link_lines("                    ", route_name, route_pattern, lang_key, item_icon, sub_item=True))
            block_lines.append(f"                </div>")
            block_lines.append(f"            </div>")
    else:
        for item in group['items']:
            resolved = resolve_item(module, icon, item)
            if resolved is None:
                continue
            route_name, route_pattern, lang_key, item_icon = resolved
            block_lines.extend(link_lines("            ", route_name, route_pattern, lang_key, item_icon, sub_item=False))

    block_lines.append(f"        <?php endif; ?>")
    block_lines.append(f"        <!-- END {module} -->")
    
    html_block = "\n".join(block_lines) + "\n"
    
    # 2. Patch sidebar.php idempotently, preserving the current indentation.
    block_pattern = rf"(?ms)^([ \t]*)<!-- START {re.escape(module)} -->.*?^[ \t]*<!-- END {re.escape(module)} -->\n?"
    block_match = re.search(block_pattern, sidebar_content)
    if block_match:
        indent = block_match.group(1)
        html_block = "\n".join(
            line.replace("        ", indent, 1) if line.startswith("        ") else line
            for line in block_lines
        ) + "\n"
        sidebar_content = sidebar_content[:block_match.start()] + html_block + sidebar_content[block_match.end():]
        print(f"Updated sidebar menu group for {module}")
    else:
        # Insert before the dynamic anchor comment, keeping the anchor line aligned.
        anchor_pattern = r"(?m)^([ \t]*)<!-- \[(?:DYNAMIC_MODULES_ANCHOR|SCAFFOLD_MODULES_ANCHOR)\] -->[ \t]*\n?"
        anchor_match = re.search(anchor_pattern, sidebar_content)
        if anchor_match:
            indent = anchor_match.group(1)
            html_block = "\n".join(
                line.replace("        ", indent, 1) if line.startswith("        ") else line
                for line in block_lines
            ) + "\n"
            anchor_line = f"{indent}<!-- [DYNAMIC_MODULES_ANCHOR] -->\n"
            sidebar_content = sidebar_content[:anchor_match.start()] + html_block + anchor_line + sidebar_content[anchor_match.end():]
            print(f"Injected sidebar menu group for {module}")
        else:
            print("Error: Could not find <!-- [DYNAMIC_MODULES_ANCHOR] --> in sidebar.php")
            sys.exit(1)
            
    # 3. Append sidebar_label to Language files if missing
    lang_dir = f"app/Modules/{module}/Language"
    for lang in ['en', 'es']:
        lang_file = f"{lang_dir}/{lang}/{module}.php"
        if os.path.exists(lang_file):
            with open(lang_file, 'r') as lf:
                lang_content = lf.read()
                
            if "'sidebar_label'" not in lang_content and '"sidebar_label"' not in lang_content:
                # Add sidebar_label to the array
                pos = lang_content.rfind("];")
                if pos != -1:
                    # Keep the fallback stable across locales; project names are
                    # not translations and would leak template-specific branding.
                    lbl_val = module
                    new_key = f"\n    'sidebar_label' => '{lbl_val}',\n"
                    patched = lang_content[:pos] + new_key + lang_content[pos:]
                    with open(lang_file, 'w') as lf_w:
                        lf_w.write(patched)
                    print(f"Added sidebar_label translation key to {lang_file}")

with open(sidebar_file, 'w') as f:
    f.write(sidebar_content)

PYEOF

echo -e "${GREEN}✓ Sidebar registration complete!${NC}"
