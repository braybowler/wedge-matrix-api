<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
            color: #1a1a1a;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 12px;
            text-align: center;
            font-size: 13px;
        }

        th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        td:first-child {
            font-weight: 600;
            background-color: #f9fafb;
            text-align: left;
        }

        .carry {
            font-size: 12px;
            color: #6b7280;
        }

        .total {
            font-size: 13px;
        }

        .separator {
            color: #9ca3af;
            margin: 0 2px;
        }
    </style>
</head>
<body>
    <h1>{{ $wedgeMatrix->label ?? 'Wedge Matrix' }}</h1>

    <table>
        <thead>
            <tr>
                <th></th>
                @foreach ($wedgeMatrix->column_headers ?? [] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($wedgeMatrix->club_labels ?? [] as $rowIndex => $clubLabel)
                <tr>
                    <td>{{ $clubLabel }}</td>
                    @foreach ($wedgeMatrix->column_headers ?? [] as $colIndex => $header)
                        <td>
                            @php
                                $cell = $wedgeMatrix->yardage_values[$rowIndex][$colIndex] ?? null;
                                $carry = $cell['carry_value'] ?? null;
                                $total = $cell['total_value'] ?? null;
                                $display = $wedgeMatrix->selected_row_display_option ?? 'Both';
                            @endphp

                            @if ($display === 'Carry' && $carry !== null)
                                <span class="carry">{{ $carry }}</span>
                            @elseif ($display === 'Total' && $total !== null)
                                <span class="total">{{ $total }}</span>
                            @elseif ($display === 'Both' && ($carry !== null || $total !== null))
                                @if ($carry !== null)
                                    <span class="carry">{{ $carry }}</span>
                                @endif
                                @if ($carry !== null && $total !== null)
                                    <span class="separator">/</span>
                                @endif
                                @if ($total !== null)
                                    <span class="total">{{ $total }}</span>
                                @endif
                            @else
                                &mdash;
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
