## 1.2 to 1.3
- Run: `symfony console oib:upgrade:12_to_13`

During this process, you can either continue using your current (custom) file entity or switch to the new default file entity. If you decide to keep using your current (custom) file entity, you don't have to change anything. If you choose to switch to the new file entity (table `oi_file`), your current file records will not be automatically transferred to the new table `oi_file`.