# Built-in Events

The following methods can be used to do actions on specific events.

## Events for models

#### Validating

[!!] These methods receive the **model object** and the **validation object** in their params.

- `model_before_validate($params)`
- `model_after_validate($params)`

#### Saving

[!!] These methods receive the **model object** in their params.

- `model_before_save($params)`
- `model_after_save($params)`

#### Deleting

- `model_before_delete($params)`
- `model_after_delete($params)`

***

## Events for builders

[!!] These methods receive the **builder object** in their params.

#### Selecting

- `builder_before_select($params)`
- `builder_after_select($params)`

#### Inserting

- `builder_before_insert($params)`
- `builder_after_insert($params)`

#### Updating

- `builder_before_update($params)`
- `builder_after_update($params)`

#### Deleting

- `builder_before_delete($params)`
- `builder_after_delete($params)`

***

## Events for meta

[!!] These methods receive the **meta object** in their params.

- `meta_before_finalize($params)`
- `meta_after_finalize($params)`