/**
 * Wrapper plugin for @prettier/plugin-php.
 *
 * Prettier's PHP plugin has no option to control where the string
 * concatenation operator (`.`) is placed when a chain breaks across
 * multiple lines. The plugin prints it trailing:
 *
 *   $sql =
 *     'a' .
 *     'b';
 *
 * This wrapper rewrites the printed doc for `.` binary expressions so
 * the operator leads the continuation line instead:
 *
 *   $sql =
 *     'a'
 *     . 'b';
 *
 * All other operators (&&, ||, +, ...) keep the plugin's default output.
 */
import * as phpPlugin from "@prettier/plugin-php";

const phpPrint = phpPlugin.printers.php.print;

function isLineDoc(doc) {
	return doc && typeof doc === "object" && doc.type === "line";
}

// printBinaryExpression() emits each operator segment as either
// group([".", line, right]) or a bare [".", line, right] array.
function isConcatSegment(doc) {
	if (Array.isArray(doc)) {
		return doc.length >= 3 && doc[0] === "." && isLineDoc(doc[1]);
	}
	return (
		doc &&
		doc.type === "group" &&
		Array.isArray(doc.contents) &&
		doc.contents.length >= 3 &&
		doc.contents[0] === "." &&
		isLineDoc(doc.contents[1])
	);
}

function transformSegment(segment) {
	if (Array.isArray(segment)) {
		const [, breakDoc, ...rest] = segment;
		return [breakDoc, ". ", ...rest.map(transformDoc)];
	}
	const [, breakDoc, ...rest] = segment.contents;
	return {
		...segment,
		contents: [breakDoc, ". ", ...transformArray(rest)],
	};
}

function transformArray(docs) {
	const out = [];
	for (const doc of docs) {
		if (isConcatSegment(doc)) {
			// Drop the separator space the plugin puts right before the
			// operator; `line` already prints a space when flat.
			if (out.length > 0 && out[out.length - 1] === " ") {
				out.pop();
			}
			out.push(transformSegment(doc));
		} else {
			out.push(transformDoc(doc));
		}
	}
	return out;
}

function transformDoc(doc) {
	if (Array.isArray(doc)) {
		return transformArray(doc);
	}
	if (doc && typeof doc === "object") {
		const clone = { ...doc };
		for (const key of [
			"contents",
			"content",
			"breakContents",
			"flatContents",
			"trueContents",
			"falseContents",
		]) {
			if (key in clone) {
				clone[key] = transformDoc(clone[key]);
			}
		}
		return clone;
	}
	return doc;
}

export const parsers = phpPlugin.parsers;
export const languages = phpPlugin.languages;
export const options = phpPlugin.options;
export const defaultOptions = phpPlugin.defaultOptions;

export const printers = {
	php: {
		...phpPlugin.printers.php,
		print(path, options, print) {
			const printed = phpPrint(path, options, print);
			const { node } = path;

			if (node && node.kind === "bin" && node.type === ".") {
				return transformDoc(printed);
			}
			return printed;
		},
	},
};
