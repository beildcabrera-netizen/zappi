package com.example.zapirapi;

import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import java.text.DecimalFormat;

public class RealizarCompraActivity extends AppCompatActivity {

    private EditText etTelefono, etDireccion;
    private Spinner spinnerMetodoPago;
    private TextView tvTotalCompra;
    private Button btnEnviarCompra;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_realizar_compra);

        // --- Referencias a los elementos del layout ---
        etTelefono = findViewById(R.id.et_telefono);
        etDireccion = findViewById(R.id.et_direccion);
        spinnerMetodoPago = findViewById(R.id.spinner_metodo_pago);
        tvTotalCompra = findViewById(R.id.tv_total_compra);
        btnEnviarCompra = findViewById(R.id.btn_enviar_compra);

        // --- Configurar Spinner de métodos de pago ---
        String[] metodos = {"Tarjeta de Crédito", "PayPal", "Transferencia Bancaria"};
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_spinner_dropdown_item,
                metodos
        );
        spinnerMetodoPago.setAdapter(adapter);

        // --- Mostrar el total recibido del Intent ---
        double total = getIntent().getDoubleExtra("TOTAL_PEDIDO", 0.0);
        tvTotalCompra.setText(new DecimalFormat("$#,##0.00").format(total));

        // --- Acción del botón ---
        btnEnviarCompra.setOnClickListener(v -> {
            String telefono = etTelefono.getText().toString().trim();
            String direccion = etDireccion.getText().toString().trim();
            String metodo = spinnerMetodoPago.getSelectedItem().toString();

            if (telefono.isEmpty() || direccion.isEmpty()) {
                Toast.makeText(this, "Por favor, complete todos los campos", Toast.LENGTH_SHORT).show();
            } else {
                Toast.makeText(
                        this,
                        "Compra realizada con éxito mediante " + metodo,
                        Toast.LENGTH_LONG
                ).show();
                finish();
            }
        });
    }
}

