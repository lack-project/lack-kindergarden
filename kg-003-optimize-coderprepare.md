---
slugName: optimize-coderprepare
includeFiles:
- src/Coder/BL/CoderPrepare.php
- src/Coder/BL/T_PrepareMetaData.php
- src/Cog/FrontMatterFormatCog.php
- src/Cog/FilesInputCog.php
- src/Cog/PromptInputCog.php
- src/Cog/StructuredInputCog.php
- src/Cog/ContinueAfterMaxTokensCog.php
editFiles:
- src/Coder/BL/CoderPrepare.php
original_prompt: Optimiere coder:prepare
---
## Assumptions

- The CoderPrepare class is the main entry point for generating preparation tasks based on user prompts.
- The logic for file matching is duplicated in multiple classes. Introducing a shared utility function for globbing files could reduce redundancy.
- The current implementation is mostly functional but can benefit from improved modularization, clearer user messaging, and better error handling.
- The generated output file logic (`kg-XXX-slug.md`) works well but can be factored out for reuse or testing.

## Goals

Optimize the `coder:prepare` command implementation to improve:
- Code modularity
- Readability
- Maintainability
- Reusability

## Planned Changes

### 1. Extract file collection logic into a helper method
Currently, the process of resolving file paths (file check, glob check, fallback to assumed missing) is inline in the method. We'll refactor this into a private helper:

```php
private function resolveFiles(array $patterns): FilesInputCog {
    $filesCog = new FilesInputCog(getcwd(), "files", "Already existing serialized files and content referenced within the programming-prompt.");
    foreach ($patterns as $part) {
        if (is_file($part)) {
            $filesCog->addFile($part);
        } elseif (str_contains($part, '*')) {
            foreach (glob($part) ?: [] as $file) {
                $filesCog->addFile($file);
            }
        } else {
            $this->missingFiles[] = $part;
        }
    }
    return $filesCog;
}
```

Use like:

```php
$filesCog = $this->resolveFiles($programmingPrompt);
```

### 2. Extract generated filename logic

Move the logic for determining the `kg-XXX-slug.md` output filename to its own method:

```php
private function generateOutputFilename(string $slug): string {
    $files = glob("kg-*-*.md");
    $nextNum = 0;
    foreach ($files as $file) {
        $parts = explode("-", basename($file));
        $num = (int) ($parts[1] ?? 0);
        $nextNum = max($nextNum, $num);
    }
    return "kg-" . str_pad($nextNum + 1, 3, "0", STR_PAD_LEFT) . "-$slug.md";
}
```

And use as:

```php
$outFile = $this->generateOutputFilename($frontmatter->getHeader()->slugName);
```

### 3. Optional: Modularize CogWerk pipeline building

Reuse a method to build the CogWerk pipeline for prepare:

```php
private function buildPrepareCogWerk(PromptInputCog $promptCog, FilesInputCog $filesCog): CogWerk {
    $cogwerk = new CogWerk($this->reasoning ? CogWerkFlavorEnum::REASONING : CogWerkFlavorEnum::DEFAULT);
    $cogwerk->addCog(new ContinueAfterMaxTokensCog());
    $cogwerk->addCog($filesCog);
    $cogwerk->addCog($promptCog);
    $cogwerk->addCog(new FilesInputCog(__DIR__ . "/example_prepare_output.md", "example_output", "Example markdown to output (excluding headers)"));
    $cogwerk->addCog(new StructuredInputCog("programming-prepare-instructions", file_get_contents(__DIR__ . "/prepare_instructions.txt"), "Follow the"));

    foreach ($this->getConfigFileCogs() as $cog) {
        $cogwerk->addCog($cog);
    }

    $cogwerk->addCog(new DebugInputOutputCog());
    return $cogwerk;
}
```

---

## Example Prompt Enhancements

Instead of specifying raw files, the user should be encouraged to use quoted filenames or globs:

```
coder:prepare "Fix bug in database layer" src/Database/*.php src/Entity/User.php
```

Or using config:
```
coder:prepare --env dev "Refactor logging system"
```

Add validations to notify on invalid or unmatched globs, and recommend checking filenames.

---

## Summary

These refactorings improve separation of concerns in `CoderPrepare` and pave the way for reusable infrastructure shared between `coder:run`, `coder:ask`, and other commands. They also improve testability and user experience.