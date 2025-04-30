# Prepare [Name of the Task]

[Brief description of the task]

## Missing Information

<!-- Only provide this section if the task requires additional information. -->


## Tasks

- **name of task1** Short description what needs to be done (max 160 characters)
- **name of task2** Short description what needs to be done (max 160 characters)


## Overview: File changes

- **filename1.ext** Short description what needs to be done (max 160 characters)
- **filename2.ext** Short description what needs to be done (max 160 characters)

## Detail changes

### filename1.ext

**Referenced Tasks**
- **name of task1** Only if this change has to do with this task. Provide information about the change (max 160 characters)
- **name of task2** Only if this change has to do with this task. Provide information about the change (max 160 characters)

Replace 

```
public function abc() { 
   ...
   if ($condition) {
      
   }
}
```
<!-- Abbreviate where. Maybe add commends to make clear where the change should occur -->

by

```
public function abc() { 
   ...
   if ($condition) {
        $this->doSomething();
     }
    }
}
```

<!-- Provide full code snippets for the changes. -->


### filename2.ext

**Referenced Tasks**
- **name of task1** Only if this change has to do with this task. Provide information about the change (max 160 characters)
- **name of task2** Only if this change has to do with this task. Provide information about the change (max 160 characters)


#### Insert code in constructor

**Insert after**

```
public function abc() { 
   ...
   if ($condition) {
      
   }
}
```

**Add the following code:**

```
public function abc() { 
   ...
   if ($condition) {
        $this->doSomething();
     }
    }
}
```


#### Update Method signature in method xy

**New signature:**

```
public function abc() { 
   ...
   if ($condition) {
        $this->doSomething();
     }
    }
}
```
