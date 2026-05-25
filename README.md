# PDFParser Benchmarks

Generate benchmarks for popular PHP parser libraries.

First, install dependencies:

```bash
composer i
```

Then run the benchmarks:
```bash
composer benchmarks
```

Then generate the graphs:
```bash
python generate-graphs.py
```

## How this works

The workflow in `.github/workflows/benchmarks.yml` runs on every push to main, manually or daily at 17:17 UTC. It publishes two images:

![](https://prinsfrank.github.io/pdfparser-benchmarks/comparison_light.png)
![](https://prinsfrank.github.io/pdfparser-benchmarks/comparison_dark.png)

These are used in the readme of `prinsfrank/pdfparser`
