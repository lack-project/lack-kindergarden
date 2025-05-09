---
slugName: optimize-coder-prepare
includeFiles:
- src/Coder/BL/CoderPrepare.php
- src/Coder/BL/CoderEnvironmentTrait.php
- src/Coder/BL/T_PrepareMetaData.php
- src/CogWerk/CogWerk.php
- src/Cog/MultiFileInputCog.php
- src/Cog/PromptInputCog.php
- src/Cog/FileInputCog.php
- src/Cog/StructuredInputCog.php
- src/Cog/FrontMatterFormatCog.php
editFiles:
- src/Coder/BL/CoderPrepare.php
original_prompt: Optimiere coder:prepare
---
# Prepare optimize-coder-prepare

Refactor and optimize the `coder:prepare` CLI command logic in `CoderPrepare.php` to improve maintainability, reduce complexity, and standardize Cog setup.

## Tasks

- **refactor-missing-files-handling** Extract missing files logic to dedicated method for readability
- **refactor-multifileinput-loading** Isolate loop logic of file pattern resolution into separate method
- **add-default-prompt-normalization** Unify prompt text handling and trimming
- **modularize-cogwerk-construction** Extract CogWerk construction into a reusable method or factory

## Overview

- **src/Coder/BL/CoderPrepare.php** Refactor CLI command logic into cleanly separated methods with single responsibility

## Detail changes

### src/Coder/BL/CoderPrepare.php

**Referenced Tasks**
- **refactor-missing-files-handling** Move logic analyzing each prompt argument into `resolvePromptFiles(...)` method
- **refactor-multifileinput-loading** Introduce helper method `addFileOrPattern(...)` which adds individual file or pattern
- **add-default-prompt-normalization** Normalize white-space and concatenate instructions into clean string
- **modularize-cogwerk-construction** Introduce `buildCoderPrepareCogWerk(...)` method for wiring up all necessary cogs

Replace logic in `run()`:

```php
foreach ($programmingPrompt as $part) {
    if (is_file($part)) {
        $filesCog->addFile($part);
    } ...
}
```

by

```php
foreach ($this->resolvePromptFiles($programmingPrompt) as $file) {
    $filesCog->addFile($file);
}
```

Add new method:

```php
private function resolvePromptFiles(array $promptParts): array
{
    $resolved = [];
    foreach ($promptParts as $part) {
        if (is_file($part)) {
            $resolved[] = $part;
        } elseif (str_contains($part, '*')) {
            $resolved = [...$resolved, ...glob($part)];
        } else {
            $this->missingFiles[] = $part;
        }
    }
    return $resolved;
}
```

Add method for constructing cogwerk:

```php
private function buildCogWerk(string $promptText, MultiFileInputCog $filesCog, bool $reasoning): CogWerk {
    $cogwerk = new CogWerk($reasoning ? CogWerkFlavorEnum::REASONING : CogWerkFlavorEnum::DEFAULT);
    $cogwerk->addCog(new ContinueAfterMaxTokensCog());
    $cogwerk->addCog($filesCog);
    $cogwerk->addCog(new PromptInputCog("Your job is to plan / prepare the task provided as user-prompt. Follow the guides provided as programming-prepare-instructions.", $promptText));
    $cogwerk->addCog(new FileInputCog(__DIR__ . "/example_prepare_output.md", "example_output", "Example markdown to output (excluding headers)"));
    $cogwerk->addCog(new StructuredInputCog("programming-prepare-instructions", file_get_contents(__DIR__ . "/prepare_instructions.txt"), "Follow the"));

    foreach ($this->getConfigFileCogs() as $cog) {
        $cogwerk->addCog($cog);
    }

    $cogwerk->addCog(new DebugInputOutputCog());
    return $cogwerk;
}
```

Normalize prompt text:

```php
$programmingPrompt = trim(implode(" ", $programmingPrompt));
```

Apply all new methods to simplify core run logic.