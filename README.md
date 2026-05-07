# yii2-entity-acl

ACL-система для Yii2 с unix-подобной моделью прав и расширяемыми условиями доступа.

Пакет предназначен для использования совместно с `yii2-base-entity-system`,
но не имеет жёсткой зависимости от него и может применяться самостоятельно.

Основная цель — дать **прозрачную, расширяемую и предсказуемую** модель контроля доступа,
подходящую для сложных бизнес-сценариев (CRM, SaaS, back-office системы).

---

## Основные принципы

1. **Unix-like модель прав**
   - owner / group / other
   - битовые флаги операций
   - отдельные правила для сущности и для конкретной записи

2. **Allow / Deny conditions**
   - условия могут как запрещать, так и разрешать доступ
   - deny всегда имеет приоритет
   - allow может разрешить доступ даже если базовые права запрещают

3. **Расширяемость**
   - subject (кто) — через `SubjectResolverInterface`
   - when (условия) — через handler’ы условий
   - storage — через `AclStorageInterface`

4. **Детерминированный порядок принятия решений**
   - без неявной магии
   - поведение зафиксировано тестами

---

## Операции и флаги

Используются следующие битовые флаги операций:

| Операция        | Флаг |
|-----------------|------|
| list            | 1    |
| read            | 2    |
| create          | 4    |
| update          | 8    |
| delete          | 16   |
| restore         | 32   |
| permanentDelete | 64   |

Флаги комбинируются через побитовое OR.

- *delete* — логическое удаление / деактивация / soft delete
- *restore* — восстановление после мягкого удаления
- *permanentDelete* — физическое удаление записи

---

## Таблицы

### `bes_acl_record`

Хранит базовые права доступа.

Используется **одна таблица**:
- для прав на сущность (`record_id IS NULL`)
- для прав на конкретную запись (`record_id = <id>`)

Основные поля:
- `entity`
- `record_id` (NULL = правило для сущности)
- `owner_flags`
- `group_flags`
- `other_flags`
- `owner_id`
- `group_id`
- `priority`

---

### `bes_acl_condition`

Хранит дополнительные условия доступа.

Основные поля:
- `entity`
- `record_id` (NULL = условие для сущности)
- `effect` (`allow` / `deny`)
- `ops_mask`
- `subject_json`
- `when_json`
- `priority`
- `enabled`

---

## Порядок принятия решения

Для запроса доступа выполняются шаги:

1. **Определение операции**
   - операция переводится в битовую маску

2. **Базовое правило (`bes_acl_record`)**
   - сначала ищется правило для конкретной записи
   - если нет — правило для сущности
   - определяется сегмент: owner / group / other
   - проверяются флаги

3. **Conditions (`bes_acl_condition`)**
   - выбираются все условия для операции
   - применяются record-level и entity-level условия
   - проверяются `subject` и `when`
   - если сработал deny → доступ запрещён
   - если сработал allow → доступ разрешён

4. **Итог**
   - allow condition
   - иначе base allow
   - иначе deny (по умолчанию)

Deny всегда имеет приоритет над allow и base-правами.

---

## Subject (кто)

Subject описывает, **к кому применяется условие**.

Пример `subject_json`:

```json
{
  "userId": 1,
  "groupId": 10
}
```

Поддерживаемые ключи (по умолчанию):

* `userId`
* `groupId`
* `ownerId`

Разрешение этих значений выполняется через `SubjectResolverInterface`.

### SubjectResolverInterface

```php
interface SubjectResolverInterface
{
    public function resolveGroupId(int $userId, array $context = []): ?int;
    public function resolveOwnerId(AccessRequest $req): ?int;
}
```

Дефолтная реализация (`ContextSubjectResolver`) читает значения из `AccessRequest->context`.

---

## When (когда)

When описывает **условия времени/контекста**, при которых правило применяется.

Пример `when_json`:

```json
[
  { "type": "betweenHours", "from": 9, "to": 17 }
]
```

Поддержка условий реализована через handler’ы:

```php
interface ConditionHandlerInterface
{
    public function supports(string $type): bool;
    public function evaluate(array $payload, AccessRequest $req, ConditionEngine $engine): bool;
}
```

### Ссылки на другие условия

Допускается ссылка на другие conditions:

```json
[
  { "condition": [12, 15] }
]
```

Все указанные условия должны выполниться (логика AND).

---

## Кеширование

`DbAclStorage` использует **in-memory кеш** на время запроса:

* ACL записи
* список conditions
* conditions по id

Это позволяет вызывать `can()` много раз без повторных запросов к БД.

---

## Использование

```php
use illusiard\entity_acl\models\dto\AccessRequest;
use illusiard\entity_acl\AclService;

$req = new AccessRequest(
    userId: 1,
    entity: 'post',
    operation: 'read',
    recordId: '10',
    context: [
        'groupId' => 5,
        'ownerId' => 1,
    ]
);

$allowed = AclService::instance()
    ->policy()
    ->can($req);
```

---

## Тесты

Логика ACL полностью покрыта unit-тестами:

* порядок применения правил
* приоритет deny / allow
* entity-level vs record-level
* subject-resolver
* conditions

Тесты являются **частью спецификации поведения**.

---

## Статус

Пакет предоставляет **стабильное ядро ACL**.
UI, редакторы правил и визуализация намеренно не входят в состав
и должны реализовываться на уровне клиентского кода или других пакетов.

---

## Лицензия

BSD 3-Clause
