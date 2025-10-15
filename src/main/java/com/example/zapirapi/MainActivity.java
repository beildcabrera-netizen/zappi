package com.example.zapirapi;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

// Importaciones para el manejo de bordes (que tenías en tu código original)
import androidx.activity.EdgeToEdge;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

public class MainActivity extends AppCompatActivity {

    private Button buttonLogin; // Declara la variable para el botón

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Configuración de la interfaz de usuario para los bordes del sistema (EdgeToEdge)
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);

        // Mantiene el código para manejar las barras del sistema (notificaciones y navegación)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // 1. Conectar el componente de Java con el ID del XML
        // El ID del botón en tu XML es 'button_login'
        buttonLogin = findViewById(R.id.button_login);

        // 2. Establecer el escuchador (Listener) para el clic del botón
        buttonLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // 3. Definir la acción: Ir a la pantalla de Login
                // Asegúrate de que la clase 'LoginActivity' exista en tu proyecto.
                Intent intent = new Intent(MainActivity.this, LoginActivity.class);
                startActivity(intent);
            }
        });
    }
}