# ContextualLabels for Omeka Classic

**ContextualLabels** is a plugin for [Omeka Classic](https://omeka.org/classic/) that dynamically changes the **labels and descriptions** of Dublin Core fields based on the **item type** and **active language**.

NB : I shared the first working version to my use case, this is definitely not a complete work. 

This plugin is especially useful for cultural heritage, architecture, and academic projects where metadata fields need contextualized labels like:

- “Date Created” → “Monument Construction Date”
- “Description” → “Architectural Description”

---

## 🔧 Features

- ✅ Dynamic label and description overrides (admin form)
- 🌐 Language-aware (e.g. `Monument_EN.txt`, `Monument_FR.txt`)
- 👀 Public view label customization
- 🧠 Per-item-type configuration
- 🔒 IIIF and UniversalViewer-safe (no interference with manifest generation)
- 🪵 Built-in debug logging for easy troubleshooting

---

## 🗂️ How It Works

The plugin looks for CSV files in `plugins/ContextualLabels/config/` named like:

```
<ItemTypeName>_<LANG>.txt
```

### Example: `Monument_EN.txt`

```csv
Date Created,Monument Construction Date,Year of construction
Coverage,Geographical Scope,Where the monument is located
Description,Architectural Description,Describe the architectural features
```

Each row is:

```
OriginalLabel,NewLabel,Description
```

- `OriginalLabel`: must match what's displayed in the UI (e.g., "Date Created")
- `NewLabel`: the label you want to show
- `Description`: shows as a help text in the admin form

---

## 📁 File Structure

```
ContextualLabels/
├── ContextualLabelsPlugin.php
├── plugin.ini
├── config/
│   ├── Monument_EN.txt
│   └── Person_EN.txt
└── debug.log (optional, for development)
```

---

## 🚀 Installation

1. Clone or download this repository
2. Place it in your `plugins/` directory as `ContextualLabels`
3. Activate the plugin in the Omeka admin panel
4. Add your override files to `ContextualLabels/config/`

---

## 📝 Compatibility

- Tested on Omeka Classic **v3.1.2**
- Compatible with UniversalViewer and other IIIF-consuming tools
- Fully safe for multilingual public or admin use

---

## 📜 License

MIT License (or specify your own)

---

## 🙌 Credits

Developed by [medersa.ma](https://medersa.ma) for built heritage documentation in Morocco.

---

## 🐛 Debugging

Enable error logging by reviewing `plugins/ContextualLabels/debug.log`.  
Make sure your file names and labels match **exactly** (case-sensitive).

---

Feel free to contribute or fork the project. PRs welcome!
