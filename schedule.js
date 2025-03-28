/**
 * Copyright (c) Christoph M. Becker
 *
 * This file is part of Schedule_XH.
 *
 * Schedule_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Schedule_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Schedule_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

// @ts-check

/** @param {HTMLFormElement} form */
function initCallBuilder(form) {
    const name = form.elements.namedItem("name");
    if (!(name instanceof HTMLInputElement)) return;
    const showTotals = form.elements.namedItem("show_totals");
    if (!(showTotals instanceof HTMLInputElement)) return;
    const readOnly = form.elements.namedItem("read_only");
    if (!(readOnly instanceof HTMLInputElement)) return;
    const multi = form.elements.namedItem("multi");
    if (!(multi instanceof HTMLInputElement)) return;
    const options = form.elements.namedItem("options");
    if (!(options instanceof HTMLTextAreaElement)) return;
    const parseButton = form.elements.namedItem("parse");
    if (!(parseButton instanceof HTMLButtonElement)) return;
    const buildButton = form.elements.namedItem("build");
    if (!(buildButton instanceof HTMLButtonElement)) return;
    const textarea = form.elements.namedItem("plugin_call");
    if (!(textarea instanceof HTMLTextAreaElement)) return;

    parseButton.onclick = () => {
        const call = textarea.value.replace(/^\s*{{{\s*schedule\s*\(\s*|\s*\)\s*}}}\s*$/g, "");
        const args = call.split(/\s*,\s*/g);
        const arg = args.shift();
        if (arg !== undefined) {
            name.value = arg.replace(/^'|'$/g, "");
        }
        if (args[0] !== undefined && (args[0] === "true" || args[0] === "false")) {
            showTotals.checked = args.shift() === "true";
        } else {
            showTotals.checked = showTotals.defaultChecked;
        }
        if (args[0] !== undefined && (args[0] === "true" || args[0] === "false")) {
            readOnly.checked = args.shift() === "true";
        } else {
            readOnly.checked = readOnly.defaultChecked;
        }
        if (args[0] !== undefined && (args[0] === "true" || args[0] === "false")) {
            multi.checked = args.shift() === "true";
        } else {
            multi.checked = multi.defaultChecked;
        }
        /** @type {Array<string>} */
        let lines = [];
        for (let i = 0; i < args.length; i++) {
            lines.push(args[i].replace(/^'\s*|\s*'$/g, ""));
        }
        options.value = lines.join("\n");
    };

    buildButton.onclick = () => {
        if (!form.reportValidity()) return;
        /** @type {Array<string>} */
        let args = [];
        args.push("'" + name.value + "'");
        args.push(showTotals.checked ? "true" : "false");
        args.push(readOnly.checked ? "true" : "false");
        args.push(multi.checked ? "true" : "false");
        const lines = options.value.split(/\n/g);
        lines.forEach(line => {
            const trimmed = line.trim();
            if (trimmed) {
                args.push("'" + trimmed + "'");
            }
        });
        textarea.value = "{{{schedule(" + args.join(", ") + ")}}}";
    };

    textarea.onclick = () => textarea.select();
}

document.querySelectorAll(".schedule_call_builder").forEach(form => {
    if (!(form instanceof HTMLFormElement)) return;
    initCallBuilder(form);
});
