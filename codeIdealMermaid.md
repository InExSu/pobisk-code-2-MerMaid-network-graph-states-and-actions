```mermaid
graph LR
A((state_1)) -- func_1 --> B((state_2))
A -- func_2 --> C((state_3))
B -- func_3 --> D((state_4))
C -- func_4 --> D
D -- func_5 --> E((state_5))
