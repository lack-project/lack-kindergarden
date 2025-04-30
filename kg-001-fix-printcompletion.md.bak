---
slugName: fix-printcompletion
inlcudeFiles:
- src/Cli/CliApplication.php
editFiles:
- src/Cli/CliApplication.php
original_prompt: Repariere die printCompetion-methode. Sie soll für jeden gruppenbegriff
  die unterbegriffe sowie mögliche optionen ausgeben. gehe die gruppen so durch, wie
  der controller. In CliApplication. If no options are present it should treat the
  current input as filename and complete the filename always if matching filename
  is there
---
# Instructions

The goal is to repair the auto-completion method (printCompetion) in the CliApplication so that for every “Gruppenbegriff” (group node) it prints its subterms (i.e. the names of its sub‑nodes) and—for commands that provide options—the available options. The method should traverse the complete group hierarchy in the same way the command controller does. Additionally, if no options are available for the current node, the method should treat the current input as a filename prefix and complete by listing those filenames (using, for example, glob) that match the prefix.

Below is an outline of the required changes for the file where the method is defined. In our project this logic is implemented in the printCompletion() method inside CliApplication.

# Files and Classes to Modify

- File: src/Cli/CliApplication.php  
  Class: CliApplication  
  Method: printCompletion

# Implementation Details

### Objective

Modify the printCompletion method to:
- Traverse each group node just as the controller does.
- For each group node, output the current “Gruppenbegriff” (i.e. the command or group name) along with its sub-commands (children) and, if available, the option suggestions.
- If a node does not have any options defined (or if options array is empty), then treat the current node—or the current input token—as a filename prefix. Complete by querying the filesystem (using glob or similar) to list file names starting with that prefix.

### Changes

1. **Traverse the Group Hierarchy:**  
   Instead of the simple recursive printing in the original method, we introduce logic that uses the already accumulated path to show the full command (or group) context. For each node, the method should print a line containing the current input (constructed from the path).

2. **Print Options for the Node:**  
   If the current node (or action node) has available options (typically stored in an array property such as `$this->options`), list each option in two forms: its short version (if available) and its long version. Format each suggestion as the current command text appended with the option flag. For example:  
   - If an option has a short flag `-d` and a long flag `--debug`, print suggestions like:  
     `<current> -d` and `<current> --debug`

3. **Filename Completion if No Options:**  
   If the node does not define any options (or if the options array is empty), then assume the current input should be interpreted as a filename prefix. Use a file search function (for example, PHP’s `glob()`) with the current input as the prefix and print every matching filename (one per line).

4. **Recursion:**  
   For each subgroup (sub-node) in the current node’s `subNodes`, recursively call the printCompletion method. Append the current node’s name to the path for the recursive call.

### Prototype Example

Below is an example prototype for the modified printCompletion method. (This must replace the existing method in the CliApplication class.)

--------------------------------------------------
function printCompletion(CliGroupNode $node, array $path): void 
{
    // Prepare the full command name from the accumulated path and the current node
    $currentCommand = implode(' ', array_filter(array_merge($path, [$node->name])));
    echo $currentCommand . "\n";

    // If the node defines options, list them
    if (property_exists($node, 'options') && is_array($node->options) && count($node->options) > 0) {
        foreach ($node->options as $option) {
            // If a short version exists, print both; otherwise only the long option.
            if (!empty($option['short'])) {
                echo $currentCommand . " -" . $option['short'] . "\n";
            }
            echo $currentCommand . " --" . $option['name'] . "\n";
        }
    } else {
        // No options present; treat the current command token as a filename prefix.
        // Use file globbing to find matching filenames.
        $prefix = trim($node->name);
        // Alternatively, you may derive the prefix from the last token in $path or currentCommand.
        $matches = glob($prefix . '*');
        if ($matches) {
            foreach ($matches as $match) {
                echo $match . "\n";
            }
        }
    }
    
    // Recursively process each sub-node
    foreach ($node->subNodes as $subNode) {
        // Append the current node's name to the path and recurse.
        $this->printCompletion($subNode, array_merge($path, [$node->name]));
    }
}
--------------------------------------------------

### Additional Notes

- Make sure that the modified method does not output additional formatting tags; the output should be plain as required.
- The filename completion always uses the current input token as a prefix (i.e. if no options are available, then search the filesystem for matching filenames).
- Verify that the changes mimic the traversal behavior used in the command controller so that the same command hierarchy is printed.

### Example Prompts

When the user types a partial command with no options, the output should list:
- The command or group name.
- Matching subcommands if defined.
- If no options exist for that group, any file(s) starting with the input token from the command line.

This implementation ensures a robust autocomplete feature for the CLI tool.

Once you have replaced the old implementation with the modified version, test by running the CLI with the `--complete` option and various partial inputs to ensure that the groups, options, and filename completions work as intended.