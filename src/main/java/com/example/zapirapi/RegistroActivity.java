package com.example.zapirapi;

import androidx.appcompat.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;
import android.content.Intent;

public class RegistroActivity extends AppCompatActivity {

    private EditText inputNombre, inputApellido, inputCorreo, inputContrasena;
    private Button buttonRegistrar;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_registro);

        // 1. Conectar las variables de Java con los IDs del XML
        inputNombre = findViewById(R.id.edit_text_nombre);
        inputApellido = findViewById(R.id.edit_text_apellido);
        inputCorreo = findViewById(R.id.edit_text_correo_reg);
        inputContrasena = findViewById(R.id.edit_text_contrasena_reg);
        buttonRegistrar = findViewById(R.id.button_registrar);

        // 2. Lógica al hacer clic en REGISTRAR
        buttonRegistrar.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // Obtener los datos
                String nombre = inputNombre.getText().toString().trim();
                String apellido = inputApellido.getText().toString().trim();
                String correo = inputCorreo.getText().toString().trim();
                String contrasena = inputContrasena.getText().toString().trim();

                // Validación simple de que los campos no estén vacíos
                if (nombre.isEmpty() || apellido.isEmpty() || correo.isEmpty() || contrasena.isEmpty()) {
                    Toast.makeText(RegistroActivity.this,
                            "Por favor, completa todos los campos.",
                            Toast.LENGTH_SHORT).show();
                    return;
                }

                // *** AQUÍ VA LA LÓGICA DE GUARDAR EL USUARIO (Base de Datos o Firebase) ***

                // Simulación de Registro Exitoso
                Toast.makeText(RegistroActivity.this,
                        "Registro exitoso para: " + nombre,
                        Toast.LENGTH_LONG).show();

                // Navegar de vuelta a la pantalla de Login
                Intent intent = new Intent(RegistroActivity.this, LoginActivity.class);
                startActivity(intent);
                finish(); // Cierra esta actividad
            }
        });
    }
}