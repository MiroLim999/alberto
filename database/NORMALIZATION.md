# Normalization of Alberto's Pizza Database

A complete walkthrough of how the Alberto's Pizza database was normalized from a raw, unnormalized spreadsheet-style structure all the way to **Third Normal Form (3NF)**.

This document explains:

- What each normal form requires
- Why the previous form was insufficient
- How each rule was applied to our specific data
- What the tables look like after each step
- Why the resulting 3NF schema is the correct design for this application

---

## Why Normalize?

Before we begin, here is what normalization actually solves. Without it, a database suffers from three classic anomalies:

| Anomaly | What it means | Example in our pizza shop |
|---|---|---|
| **Insertion anomaly** | You can't add new data because required-but-unrelated fields force you to make things up | You can't add a new pizza category until someone orders a pizza in that category |
| **Update anomaly** | Changing one fact requires updating many rows; missing one creates inconsistency | Renaming "BORACAY" branch to "BORACAY MAIN" requires editing every order from that branch |
| **Deletion anomaly** | Deleting a row also deletes information you wanted to keep | Deleting the last order from a branch wipes out the branch's existence entirely |

Normalization eliminates these by ensuring **each fact lives in exactly one place**.

---

## UNF — Unnormalized Form

We begin with one giant flat table — the kind of structure you'd see if a non-technical owner kept everything in a single spreadsheet. Every column related to an order, customer, branch, pizza, ingredient, and pricing is crammed into one row.

### Table: `orders_raw`

| order_id | customer_name | mobile | email | branch_name | branch_location | order_type | payment | address | status | created_at | items | pizza_ingredients | pizza_image | pizza_stock | category | size_9_quickmelt | size_9_mozzarella | size_11_quickmelt | size_11_mozzarella |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | Guest | 09876543210 | gil@x.com | BORACAY | Aklan | DELIVERY | CASH | Zone 5 | pending | 2026-05-20 | "Pizza Supreme, 11", Quickmelt, 1, 195 ; Cookies N Cheese, 9", Quickmelt, 1, 110" | "Pork pepperoni, Bacon, Mushroom, Onions, Pineapple tidbits" | menu/.../Supreme.png | 8 | Bestsellers | 145 | 165 | 195 | 215 |
| 3 | Moxie | 09876543213 | moxie@x.com | BAYBAY | Leyte | DELIVERY | ONLINE | Zone 1 | out_for_delivery | 2026-05-20 | "Hawaiian, 9", Quickmelt, 2 ; Aloha, 11", Mozzarella, 1" | "Ham, Pineapple tidbits, Mozzarella" | menu/.../Hawaiian.png | 8 | Bestsellers | 145 | 165 | 195 | 215 |

### What's wrong with this table

This table breaks fundamental relational rules in five different ways:

1. **Multi-valued cells.** The `items` column contains multiple pizzas in one cell, separated by semicolons. The same is true of `pizza_ingredients` — multiple ingredients are jammed into one comma-separated string.

2. **Repeating groups disguised as columns.** The four price columns (`size_9_quickmelt`, `size_9_mozzarella`, `size_11_quickmelt`, `size_11_mozzarella`) all describe the same kind of thing — a price for a size/cheese combination. This is a "repeating group" hidden in the schema. If we add a 13" pizza later we have to alter the table.

3. **Massive duplication.** Every column except `order_id` repeats whenever the customer places another order. The branch's location is restated for every single order from that branch. The pizza's category is restated for every order containing that pizza.

4. **No way to query meaningfully.** You can't write `SELECT SUM(...) FROM orders_raw WHERE pizza = 'Hawaiian'` because pizzas are buried inside a string. Every aggregation requires fragile string parsing.

5. **No primary key.** Without atomic columns, no candidate key exists that uniquely identifies a row.

This is not yet a database — it's a spreadsheet pretending to be one.

---

## Step 1: 1NF — First Normal Form

### Rule

> A relation is in **1NF** when:
>
> 1. Every cell contains a single atomic (indivisible) value.
> 2. Every row is unique (a primary key exists).
> 3. There are no repeating groups of columns.

### How we applied 1NF to `orders_raw`

We performed three transformations to make every cell atomic:

**(a) Split multi-item orders into separate rows.** The single row for order #1 (which contained two pizzas) becomes two rows — one per pizza.

**(b) Split the comma-separated ingredient string into separate rows.** Pizza Supreme has 5 ingredients listed; this becomes 5 rows when joined to that pizza.

**(c) Pivot the four price columns into rows.** Instead of `size_9_quickmelt = 145, size_11_quickmelt = 195, ...` we now have separate rows of `(size, cheese, price)`.

After flattening, every cell holds exactly one value. The table is technically still one table at this point — we have not yet split it into multiple — but the data inside it is now atomic.

### Table: `orders_1nf` (after flattening)

| order_id | customer_name | mobile | email | branch_name | branch_location | order_type | payment | address | status | pizza_name | size | cheese | price | quantity | ingredient | pizza_image | category | stock |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | Guest | 09876543210 | gil@x.com | BORACAY | Aklan | DELIVERY | CASH | Zone 5 | pending | Pizza Supreme | 11 | Quickmelt | 195 | 1 | Pork pepperoni | Supreme.png | Bestsellers | 8 |
| 1 | Guest | 09876543210 | gil@x.com | BORACAY | Aklan | DELIVERY | CASH | Zone 5 | pending | Pizza Supreme | 11 | Quickmelt | 195 | 1 | Bacon | Supreme.png | Bestsellers | 8 |
| 1 | Guest | 09876543210 | gil@x.com | BORACAY | Aklan | DELIVERY | CASH | Zone 5 | pending | Pizza Supreme | 11 | Quickmelt | 195 | 1 | Mushroom | Supreme.png | Bestsellers | 8 |
| 1 | Guest | 09876543210 | gil@x.com | BORACAY | Aklan | DELIVERY | CASH | Zone 5 | pending | Cookies N Cheese | 9 | Quickmelt | 110 | 1 | Choco stick | Cookies.png | Kiddies | 9 |
| 3 | Moxie | 09876543213 | moxie@x.com | BAYBAY | Leyte | DELIVERY | ONLINE | Zone 1 | out_for_delivery | Hawaiian | 9 | Quickmelt | 145 | 2 | Ham | Hawaiian.png | Bestsellers | 8 |

**Composite primary key:** `(order_id, pizza_name, size, cheese, ingredient)` — together these five columns uniquely identify a row.

### Did we satisfy 1NF?

| 1NF requirement | Status |
|---|---|
| Atomic cells | ✅ Yes — no more semicolons, commas, or multi-value strings |
| Unique rows | ✅ Yes — composite key uniquely identifies every row |
| No repeating groups | ✅ Yes — the four size/cheese price columns have been replaced with rows |

### Why 1NF alone is not enough

Look closely at the duplication. For every ingredient of a pizza, the **entire customer info** is restated. For every line of order #1, the customer name, mobile, email, address, branch name, and branch location are all copied. If Moxie changes her phone number, we have to update every row in every order she's ever placed.

This duplication points to **partial dependencies**, which is the next step's concern.

---

## Step 2: 2NF — Second Normal Form

### Rule

> A relation is in **2NF** when:
>
> 1. It is already in 1NF.
> 2. Every non-key attribute is **fully functionally dependent** on the **whole** primary key — no partial dependencies on just a part of a composite key.

A partial dependency means a column depends on **only part** of the composite key, not all of it.

### Identifying partial dependencies in `orders_1nf`

The composite key is `(order_id, pizza_name, size, cheese, ingredient)`. Let's audit each non-key column:

| Non-key attribute | Truly depends on… | Is this the whole key? |
|---|---|---|
| `customer_name` | `order_id` only | ❌ Partial |
| `mobile` | `order_id` only | ❌ Partial |
| `email` | `order_id` only | ❌ Partial |
| `address` | `order_id` only | ❌ Partial |
| `branch_name` | `order_id` only | ❌ Partial |
| `branch_location` | `order_id` only | ❌ Partial |
| `order_type` | `order_id` only | ❌ Partial |
| `payment` | `order_id` only | ❌ Partial |
| `status` | `order_id` only | ❌ Partial |
| `created_at` | `order_id` only | ❌ Partial |
| `price` | `(pizza_name, size, cheese)` | ❌ Partial |
| `pizza_image` | `pizza_name` only | ❌ Partial |
| `category` | `pizza_name` only | ❌ Partial |
| `stock` | `pizza_name` only | ❌ Partial |
| `quantity` | `(order_id, pizza_name, size, cheese)` | ⚠️ Almost full — but not on `ingredient` |
| `ingredient` | `pizza_name` only | ❌ Partial |

Every non-key column has a partial dependency. This is why normalizing pays off so dramatically here — splitting these into separate tables eliminates almost all the duplication.

### How we applied 2NF

We split `orders_1nf` into multiple tables, where each table has a key that the non-key attributes truly depend on:

**(a)** Order-level info (customer, branch, status, etc.) goes into an `orders` table keyed on `order_id`.

**(b)** Pizza-level info (image, category, stock) goes into a `pizzas` table keyed on `pizza_name`.

**(c)** Pricing info goes into a `pizza_variants` table keyed on `(pizza_name, size, cheese)`.

**(d)** Ingredient relationships go into a `pizza_ingredients` table keyed on `(pizza_name, ingredient)`.

**(e)** The actual link between an order and the pizzas in it (with quantities) goes into an `order_items` table.

### Tables produced after 2NF

#### Table: `orders` (2NF)

| order_id 🔑 | customer_name | mobile | email | branch_name | branch_location | order_type | payment | address | status | created_at |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | Guest | 09876543210 | gil@x.com | BORACAY | Aklan | DELIVERY | CASH | Zone 5 | pending | 2026-05-20 |
| 3 | Moxie | 09876543213 | moxie@x.com | BAYBAY | Leyte | DELIVERY | ONLINE | Zone 1 | out_for_delivery | 2026-05-20 |
| 11 | customer2 | 09876543213 | customer2@x.com | BAYBAY | Leyte | DELIVERY | ONLINE | Zone 5 | delivered | 2026-05-20 |

Every column depends on `order_id` alone — no more partial dependencies on item-level columns.

#### Table: `pizzas` (2NF)

| pizza_name 🔑 | category | image_path | stock |
|---|---|---|---|
| Pizza Supreme | Bestsellers | menu/.../Pizza Supreme.png | 8 |
| Cookies N Cheese | Kiddies Favorites | menu/.../Cookies N Cheese.png | 9 |
| Hawaiian | Bestsellers | menu/.../Hawaiian.png | 8 |
| Aloha | Bestsellers | menu/.../Aloha.png | 9 |

Every column depends on `pizza_name`.

#### Table: `pizza_variants` (2NF)

| pizza_name 🔑 | size 🔑 | cheese 🔑 | price |
|---|---|---|---|
| Pizza Supreme | 9 | Quickmelt | 145.00 |
| Pizza Supreme | 9 | Mozzarella | 165.00 |
| Pizza Supreme | 11 | Quickmelt | 195.00 |
| Pizza Supreme | 11 | Mozzarella | 215.00 |
| Hawaiian | 9 | Quickmelt | 145.00 |
| Hawaiian | 11 | Mozzarella | 215.00 |

`price` depends on the whole composite key `(pizza_name, size, cheese)`. Notice how each pizza now has up to 4 rows here, one per size/cheese combination — each carrying a single price.

#### Table: `pizza_ingredients` (2NF)

| pizza_name 🔑 | ingredient 🔑 |
|---|---|
| Pizza Supreme | Pork pepperoni |
| Pizza Supreme | Bacon |
| Pizza Supreme | Mushroom |
| Pizza Supreme | Onions |
| Pizza Supreme | Pineapple tidbits |
| Hawaiian | Ham |
| Hawaiian | Pineapple tidbits |
| Hawaiian | Mozzarella |

This is a pure many-to-many relationship table. Each row says "this pizza contains this ingredient."

#### Table: `order_items` (2NF)

| order_id 🔑 | pizza_name 🔑 | size 🔑 | cheese 🔑 | quantity |
|---|---|---|---|---|
| 1 | Pizza Supreme | 11 | Quickmelt | 1 |
| 1 | Cookies N Cheese | 9 | Quickmelt | 1 |
| 3 | Hawaiian | 9 | Quickmelt | 2 |
| 3 | Aloha | 11 | Mozzarella | 1 |

`quantity` depends on the whole key.

### Did we satisfy 2NF?

| 2NF requirement | Status |
|---|---|
| Already in 1NF | ✅ Yes |
| No partial dependencies | ✅ Yes — every non-key column in every table now depends on its full primary key |

### Why 2NF alone is not enough

We solved partial dependencies, but a different type of redundancy remains: **transitive dependencies**.

In `orders`, look at `branch_location`. It does technically depend on `order_id` (an order is at one branch), but really `branch_location` depends on `branch_name`, which in turn depends on `order_id`. This is `order_id → branch_name → branch_location` — a chain.

If two orders are at the same branch, we duplicate the branch's location across both rows. If the branch moves, we have to update every order. That's a transitive dependency.

The same pattern appears in `pizzas` (where `category` is just a label, repeated across many pizzas) and in `pizza_ingredients` (where the ingredient name is repeated for every pizza that uses it).

---

## Step 3: 3NF — Third Normal Form

### Rule

> A relation is in **3NF** when:
>
> 1. It is already in 2NF.
> 2. There are no **transitive dependencies** — every non-key attribute depends only on the primary key, not on another non-key attribute.

In simpler terms: every column must describe **the key, the whole key, and nothing but the key.** (This is a famous mnemonic for 3NF.)

### Identifying transitive dependencies

| Table | Transitive dependency | Why it's a problem |
|---|---|---|
| `orders` | `order_id → branch_name → branch_location` | The same `(branch_name, branch_location)` pair repeats for every order at that branch |
| `orders` | `order_id → user_id → (mobile, email)` for registered customers | If the customer updates their phone number, we'd have to update every past order |
| `pizzas` | `pizza_name → category` | "Bestsellers" label repeats across many rows. Renaming the category is a multi-row update |
| `pizza_ingredients` | `(pizza_name, ingredient_name) → ingredient_name` | Ingredient names like "Mozzarella" appear in many pizzas. Spelling correction = many updates |

### How we applied 3NF

We extract each transitively dependent attribute into its own lookup table, replacing the dependent column with a foreign key.

**(a) Branches:** Extract `branch_name` and `branch_location` into a `branches` table. `orders` keeps only `branch_id` as a foreign key.

**(b) Categories:** Extract `category` into a `categories` table. `pizzas` keeps only `category_id`.

**(c) Ingredients:** Extract `ingredient_name` into an `ingredients` table. `pizza_ingredients` becomes a junction table between `pizzas` and `ingredients` using IDs.

**(d) Users:** The customer-related fields in orders make sense only when stored once per customer. We create a `users` table that holds username, password, role, mobile, email, etc.

**(e) Order contacts:** Walk-in customers don't have a `users` row, but we still need their contact info. We create an `order_contacts` table — a one-to-one extension of `orders` that holds the guest's name, phone, and email when there's no `user_id`. This neatly separates "registered customer" from "guest contact" without forcing one into the other.

**(f) Surrogate keys:** Every table gets an integer auto-increment primary key (`branch_id`, `pizza_id`, `variant_id`, etc.). This makes joins efficient and makes renaming any natural attribute (like a pizza name) safe — we don't have to cascade the change through every related table.

**(g) Computed values are not stored.** We deliberately do **not** store `total_amount` on the `orders` table or `price`/`total` on `order_items`. These can be derived at query time:
`SUM(oi.quantity × pv.price)` joined through `order_items → pizza_variants`. Storing them would create a different kind of redundancy and risk inconsistency (item prices change but the stored total doesn't).

### Final 3NF Schema

#### Table: `branches`

| branch_id 🔑 | branch_name | location |
|---|---|---|
| 41 | BORACAY | Aklan |
| 89 | BAYBAY | Leyte |
| 93 | SOGOD | Southern Leyte |
| 99 | BORONGAN | Eastern Samar |

#### Table: `categories`

| category_id 🔑 | category_name |
|---|---|
| 1 | Bestsellers |
| 2 | Kiddies Favorites |
| 3 | House Specialties |
| 4 | Other Flavors |
| 5 | New Flavors |

#### Table: `users`

| user_id 🔑 | username | password | role | birth_date | gender | mobile_number | email |
|---|---|---|---|---|---|---|---|
| 1 | customer1 | customer1 | customer | 2000-01-01 | Female | 09876543210 | customer1@gmail.com |
| 2 | cashier1 | cashier1 | cashier | 1999-01-01 | Other | 09876543211 | cashier1@gmail.com |
| 3 | admin1 | admin1 | admin | 1998-01-01 | Female | 09876543212 | admin1@gmail.com |
| 9 | driver1 | driver1 | driver | 2009-10-17 | Male | 09876543211 | driver1@gmail.com |

#### Table: `pizzas`

| pizza_id 🔑 | pizza_name | category_id 🔗 | image_path | stock |
|---|---|---|---|---|
| 1 | Pizza Supreme | 1 | menu/.../Pizza Supreme.png | 8 |
| 3 | Cookies N Cheese | 2 | menu/.../Cookies N Cheese.png | 9 |
| 26 | Hawaiian | 1 | menu/.../Hawaiian.png | 8 |
| 27 | Aloha | 1 | menu/.../Aloha.png | 9 |

#### Table: `pizza_variants`

| variant_id 🔑 | pizza_id 🔗 | size | cheese | price |
|---|---|---|---|---|
| 1 | 1 | 9 | Quickmelt | 145.00 |
| 2 | 1 | 9 | Mozzarella | 165.00 |
| 3 | 1 | 11 | Quickmelt | 195.00 |
| 4 | 1 | 11 | Mozzarella | 215.00 |
| 91 | 26 | 9 | Quickmelt | 145.00 |
| 95 | 27 | 9 | Quickmelt | 150.00 |

A `UNIQUE(pizza_id, size, cheese)` constraint prevents duplicate variants.

#### Table: `ingredients`

| ingredient_id 🔑 | ingredient_name |
|---|---|
| 1 | Pork pepperoni |
| 2 | Bacon |
| 3 | Mushroom |
| 4 | Onions |
| 5 | Pineapple tidbits |
| 17 | Ham |
| 23 | Mozzarella |

#### Table: `pizza_ingredients`

A pure M:N junction table between `pizzas` and `ingredients`.

| pizza_id 🔑🔗 | ingredient_id 🔑🔗 |
|---|---|
| 1 | 1 |
| 1 | 2 |
| 1 | 3 |
| 1 | 4 |
| 1 | 5 |
| 26 | 17 |
| 26 | 5 |
| 26 | 23 |

#### Table: `orders`

No customer info, no totals, no branch name — those have all been extracted.

| order_id 🔑 | user_id 🔗 | branch_id 🔗 | address | order_type | payment_method | status | created_at | driver_id 🔗 | updated_at |
|---|---|---|---|---|---|---|---|---|---|
| 1 | NULL | 93 | Zone 5 | PICK-UP | CASH | cancelled | 2026-05-20 | NULL | NULL |
| 3 | NULL | 89 | Zone 1 | DELIVERY | ONLINE | out_for_delivery | 2026-05-20 | 9 | 2026-05-27 |
| 11 | 4 | 89 | Zone 5 | DELIVERY | ONLINE | delivered | 2026-05-20 | 9 | 2026-05-27 |

#### Table: `order_contacts`

Holds guest contact info — only populated when `orders.user_id IS NULL`.

| order_id 🔑🔗 | customer_name | mobile_number | email |
|---|---|---|---|
| 1 | Guest | 09876543210 | gil@x.com |
| 3 | Moxie | 09876543213 | moxie@x.com |

#### Table: `order_items`

Only `variant_id` and `quantity` — price is derived from `pizza_variants` at query time.

| item_id 🔑 | order_id 🔗 | variant_id 🔗 | quantity |
|---|---|---|---|
| 1 | 1 | 101 | 1 |
| 5 | 3 | 108 | 2 |
| 6 | 3 | 78 | 1 |

### Did we satisfy 3NF?

For each table, we check that every non-key attribute depends on the primary key and nothing else:

| Table | Non-key attributes depend on… | 3NF? |
|---|---|---|
| `branches` | `branch_id` directly | ✅ |
| `categories` | `category_id` directly | ✅ |
| `users` | `user_id` directly | ✅ |
| `pizzas` | `pizza_id` directly (category accessed via FK) | ✅ |
| `pizza_variants` | `variant_id` directly | ✅ |
| `ingredients` | `ingredient_id` directly | ✅ |
| `pizza_ingredients` | nothing — pure junction table | ✅ |
| `orders` | `order_id` directly (branch via FK, user via FK) | ✅ |
| `order_contacts` | `order_id` directly | ✅ |
| `order_items` | `item_id` directly | ✅ |

Every transitive dependency has been removed. Every column describes its key directly.

---

## How the Anomalies Are Now Solved

Recall the three anomalies we set out to fix:

| Anomaly | Old behavior | New behavior |
|---|---|---|
| **Insertion** | Adding a new branch required a fake order; adding a new pizza category required a fake pizza | New branches insert into `branches`, new categories into `categories` — independent of any order or pizza |
| **Update** | Renaming "BORACAY" branch required updating every order from that branch | Update one row in `branches` — every order now reflects the change automatically through the `branch_id` FK |
| **Deletion** | Deleting the last order from a branch erased the branch's existence | Branches live in their own table; orders coming and going don't affect them |

---

## Computed Fields — Why They're Not Stored

You might wonder: *"Why not just save the order's `total_amount`? It would be faster to read."*

This was a deliberate 3NF decision. Here's the reasoning:

```sql
SELECT SUM(oi.quantity * pv.price) AS total_amount
FROM order_items oi
JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
WHERE oi.order_id = ?;
```

This computes the total at query time from the source data. If we had stored `total_amount` directly:

- A price change in `pizza_variants` would not update existing orders (which is actually desired — prices are historical) **but** if we ever needed to recompute or audit, the stored total could be wrong while the items remain.
- Two sources of truth = a chance of disagreement.
- The total is fast to compute with a proper index on `oi.order_id`.

The same reasoning applies to `customer_name` on the `orders` table — we removed it because it's available via `JOIN` to `users` or `order_contacts`.

---

## Final Schema Summary

```
┌─────────────┐      ┌─────────────┐      ┌──────────────┐
│  branches   │      │ categories  │      │ ingredients  │
└──────┬──────┘      └──────┬──────┘      └──────┬───────┘
       │                    │                    │
       │                    │                    │
       │             ┌──────▼──────┐      ┌──────▼─────────────┐
       │             │   pizzas    ├──────► pizza_ingredients  │
       │             └──────┬──────┘      └────────────────────┘
       │                    │
       │             ┌──────▼─────────┐
       │             │ pizza_variants │
       │             └──────┬─────────┘
       │                    │
       │             ┌──────▼─────────┐
       │             │  order_items   │
       │             └──────┬─────────┘
       │                    │
┌──────▼──────┐      ┌──────▼──────┐      ┌──────────────────┐
│   users     ├──────►   orders    ├──────►  order_contacts  │
└─────────────┘      └─────────────┘      └──────────────────┘
       ▲                    │
       │                    │
       └────────────────────┘
        (driver_id FK)
```

10 tables, each with a clear purpose, each satisfying 3NF, each connected through proper foreign keys.

---

## Legend

| Symbol | Meaning |
|---|---|
| 🔑 | Primary key |
| 🔗 | Foreign key |
| ✅ | Compliant with the rule |
| ❌ | Violates the rule |
| 🚫 | Issue / anomaly |
| ⚠️ | Partial compliance / borderline |
