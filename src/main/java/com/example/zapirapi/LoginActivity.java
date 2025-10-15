package com.example.zapirapi;
 // Reemplaza esto con el nombre de tu paquete

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

public class LoginActivity extends AppCompatActivity {

    // 1. Declarar los componentes de la interfaz
    private EditText editTextCorreo;
    private EditText editTextContrasena;
    private Button buttonEntrar;
    private TextView textViewRegistrarte;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        // Carga el diseño que creaste en XML
        setContentView(R.layout.activity_login);

        // 2. Conectar las variables de Java con los IDs del XML
        editTextCorreo = findViewById(R.id.edit_text_correo);
        editTextContrasena = findViewById(R.id.edit_text_contrasena);
        buttonEntrar = findViewById(R.id.button_entrar);
        textViewRegistrarte = findViewById(R.id.text_view_registrarte);

        // 3. Lógica del botón "ENTRAR"
        buttonEntrar.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // Obtener el texto que el usuario ingresó
                String correo = editTextCorreo.getText().toString().trim();
                String contrasena = editTextContrasena.getText().toString().trim();

                // Lógica de Validación Básica
                if (correo.isEmpty() || contrasena.isEmpty()) {
                    // Mostrar un mensaje si falta algún campo
                    Toast.makeText(LoginActivity.this,
                            "Por favor, ingresa tu correo y contraseña.",
                            Toast.LENGTH_SHORT).show();
                } else {
                    // *** AQUÍ VA LA LÓGICA DE CONEXIÓN REAL (Firebase, Base de Datos, etc.) ***
                    // Por ahora, solo simula un inicio de sesión exitoso

                    if (correo.equals("1") && contrasena.equals("1")) {
                        // Si el login es exitoso, navegar a la pantalla principal (Catálogo)
                        Toast.makeText(LoginActivity.this,
                                "¡Bienvenido a RAPI ZAPI!",
                                Toast.LENGTH_SHORT).show();

                        // Necesitas crear una clase llamada 'CatalogoActivity'
                        Intent intent = new Intent(LoginActivity.this, CatalogoActivity.class);
                        startActivity(intent);
                        finish(); // Cierra esta actividad para que el usuario no pueda volver con el botón "Atrás"
                    } else {
                        Toast.makeText(LoginActivity.this,
                                "Credenciales incorrectas. Intenta de nuevo.",
                                Toast.LENGTH_SHORT).show();
                    }
                }
            }
        });

        // 4. Lógica para ir a la pantalla de "REGISTRARSE"
        textViewRegistrarte.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // Necesitas crear una clase llamada 'RegistroActivity'
                Intent intent = new Intent(LoginActivity.this, RegistroActivity.class);
                startActivity(intent);
            }
        });
    }
}