package com.example.zapirapi;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageButton; // Para el botón de Carrito
import android.widget.Spinner;
import android.widget.Toast;
import android.widget.AdapterView;

public class CatalogoActivity extends AppCompatActivity {

    private RecyclerView recyclerView;
    private Spinner spinnerCategoria, spinnerTalla, spinnerColor;
    private ImageButton buttonCarrito; // Carrito
    private Button buttonAplicarFiltros; // Aplicar Filtros

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_catalogo);

        // 1. Conectar las vistas
        recyclerView = findViewById(R.id.recycler_view_productos);
        spinnerCategoria = findViewById(R.id.spinner_categoria);
        spinnerTalla = findViewById(R.id.spinner_talla);
        spinnerColor = findViewById(R.id.spinner_color);
        buttonAplicarFiltros = findViewById(R.id.button_aplicar_filtros);
        buttonCarrito = findViewById(R.id.button_carrito); // Conexión del ImageButton

        // 2. Configuración Mínima del RecyclerView (para que la app corra)
        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        // 3. Lógica del botón APLICAR FILTROS
        buttonAplicarFiltros.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                aplicarFiltrosSeleccionados();
            }
        });

        // 4. Lógica para el botón de Carrito
        buttonCarrito.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Toast.makeText(CatalogoActivity.this,
                        "Ir al Carrito de Compras...",
                        Toast.LENGTH_SHORT).show();
                // Aquí iría el Intent a ArticulosAnadidosActivity.class
                Intent intent = new Intent(CatalogoActivity.this, ArticuloAnadidosActivity.class);
                startActivity(intent);
            }
        });
    }

    /**
     * Función que lee la selección de todos los Spinners y simula la acción de filtrado.
     */
    private void aplicarFiltrosSeleccionados() {
        // Obtener la selección de cada Spinner
        String categoria = spinnerCategoria.getSelectedItem().toString();
        String talla = spinnerTalla.getSelectedItem().toString();
        String color = spinnerColor.getSelectedItem().toString();
        // Puedes obtener el texto de búsqueda aquí también si quieres

        String mensaje = "Filtros aplicados:\n" +
                "Categoría: " + categoria + "\n" +
                "Talla: " + talla + "\n" +
                "Color: " + color;

        Toast.makeText(this, mensaje, Toast.LENGTH_LONG).show();

        // *** AQUÍ IRÍA LA LÓGICA REAL PARA FILTRAR EL RECYCLERVIEW ***
    }
}