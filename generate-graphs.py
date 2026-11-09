import plotly.graph_objects as go
from plotly.subplots import make_subplots
import json

with open('public/benchmarks_total.json', 'r') as f:
    data = json.load(f)

libraries = [key for key in data.keys() if key != 'environment']
colors = ['#e74c3c', '#2ecc71']

def create_figure(theme: str) -> go.Figure:
    is_dark = theme == 'dark'

    background_color = '#111827' if is_dark else 'white'
    plot_background_color = '#111827' if is_dark else 'white'
    text_color = '#f9fafb' if is_dark else '#111827'
    grid_color = '#374151' if is_dark else '#ecf0f1'
    annotation_color = '#4ade80' if is_dark else '#27ae60'

    fig = make_subplots(
        rows=1,
        cols=3,
        subplot_titles=(
            'Execution Time (ms)',
            'Memory Usage (MB)',
            'Successful parse (%)'
        ),
        horizontal_spacing=0.12
    )

    fig.add_trace(
        go.Bar(
            x=libraries,
            y=[data[lib]['ms']['median'] for lib in libraries],
            error_y={
                'type': 'data',
                'array': [
                    data[lib]['ms']['p95'] - data[lib]['ms']['median'] for lib in libraries
                ],
                'arrayminus': [
                    data[lib]['ms']['median'] - data[lib]['ms']['p5'] for lib in libraries
                ],
                'visible': True,
                'color': text_color,
            },
            marker={
                'color': colors,
                'line': {
                    'width': 0,
                },
            },
            text=[f"{data[lib]['ms']['median']:.1f} ms" for lib in libraries],
            textposition='outside',
            textfont={'color': text_color},
            showlegend=False
        ),
        row=1,
        col=1
    )

    fig.add_trace(
        go.Bar(
            x=libraries,
            y=[data[lib]['bytes']['median'] / 1_000_000 for lib in libraries],
            error_y={
                'type': 'data',
                'array': [
                    (data[lib]['bytes']['p95'] - data[lib]['bytes']['median']) / 1_000_000 for lib in libraries
                ],
                'arrayminus': [
                    (data[lib]['bytes']['median'] - data[lib]['bytes']['p5']) / 1_000_000 for lib in libraries
                ],
                'visible': True,
                'color': text_color,
            },
            marker={
                'color': colors,
                'line': {
                    'width': 0,
                },
            },
            text=[f"{data[lib]['bytes']['median'] / 1_000_000:.2f} MB" for lib in libraries],
            textposition='outside',
            textfont={'color': text_color},
            showlegend=False
        ),
        row=1,
        col=2
    )

    fig.add_trace(
        go.Bar(
            x=libraries,
            y=[data[lib]['pass'] for lib in libraries],
            marker={
                'color': colors,
                'line': {
                    'width': 0,
                },
            },
            text=[f"{data[lib]['pass']:.2f}%" for lib in libraries],
            textposition='outside',
            textfont={'color': text_color},
            showlegend=False
        ),
        row=1,
        col=3
    )

    annotations = list(fig.layout.annotations)
    annotations.extend([
        {
            'x': lib,
            'y': data[lib]['ms']['p95'],
            'text': f"p95: {data[lib]['ms']['p95']:.2f}",
            'showarrow': False,
            'yshift': 10,
            'xref': 'x',
            'yref': 'y',
        }
        for lib in libraries
    ])
    annotations.extend([
        {
            'x': lib,
            'y': data[lib]['bytes']['p95'] / 1_000_000,
            'text': f"p95: {data[lib]['bytes']['p95'] / 1_000_000:.2f}",
            'showarrow': False,
            'yshift': 10,
            'xref': 'x2',
            'yref': 'y2',
        }
        for lib in libraries
    ])

    fig.update_layout(
        annotations = annotations,
        plot_bgcolor=plot_background_color,
        paper_bgcolor=background_color,
        font={
            'family': 'Arial, sans-serif',
            'size': 12,
            'color': text_color,
        },
        height=500,
        width=1200,
        margin=dict(t=110, b=80, l=60, r=60)
    )

    fig.update_xaxes(
        tickangle=0,
        tickfont={'size': 10, 'color': text_color},
        linecolor=grid_color,
        gridcolor=grid_color,
        zerolinecolor=grid_color,
    )

    fig.update_yaxes(
        gridcolor=grid_color,
        gridwidth=1,
        tickfont={'color': text_color},
        linecolor=grid_color,
        zerolinecolor=grid_color,
    )

    fig.update_yaxes(range=[0, max([data[lib]['ms']['p95'] for lib in libraries]) * 1.15], row=1, col=1)
    fig.update_yaxes(range=[0, max([data[lib]['bytes']['p95'] / 1_000_000 for lib in libraries]) * 1.05], row=1, col=2)
    fig.update_yaxes(range=[0, 105], row=1, col=3)

    speedup = data['smalot/pdfparser']['ms']['median'] / data['prinsfrank/pdfparser']['ms']['median']
    memory_reduction = data['smalot/pdfparser']['bytes']['median'] / data['prinsfrank/pdfparser']['bytes']['median']

    fig.add_annotation(
        x=0.5,
        y=1.25,
        xref='paper',
        yref='paper',
        text=(
            f"prinsfrank/pdfparser is <b>{speedup:.1f}x faster</b> "
            f"and uses <b>{memory_reduction:.1f}x less memory</b> "
            f"with a <b>higher success rate</b>"
        ),
        showarrow=False,
        font={'size': 20, 'color': annotation_color},
        align='center'
    )

    fig.add_annotation(
        x=0.5,
        y=-0.23,
        xref='paper',
        yref='paper',
        text='*based on all non-corrupted test samples in both libraries, see benchmark setup on github.com/prinsfrank/pdfparser-benchmarks',
        showarrow=False,
        font={'size': 9, 'color': text_color},
        align='center'
    )

    return fig

light_fig = create_figure('light')
light_fig.write_image("./public/comparison_light.png", scale=2)

dark_fig = create_figure('dark')
dark_fig.write_image("./public/comparison_dark.png", scale=2)
