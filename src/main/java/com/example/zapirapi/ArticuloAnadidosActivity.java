package com.example.zapirapi;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;

public class ArticuloAnadidosActivity extends AppCompatActivity {

    private Button btnFinalizarCompra;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_articulo_anadidos);

        // Conectar solo el botón (los demás campos aún no se usan)
        btnFinalizarCompra = findViewById(R.id.btn_finalizar_compra);

        // Evento: al presionar el botón ir a RealizarCompraActivity
        btnFinalizarCompra.setOnClickListener(v -> {
            Intent intent = new Intent(ArticuloAnadidosActivity.this, RealizarCompraActivity.class);
            startActivity(intent);
        });
    }
}

/*
package com.example.zapirapi;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;
import java.util.ArrayList;
import java.util.List;

public class ArticulosAnadidosActivity extends AppCompatActivity
        implements CarritoAdapter.OnCarritoListener {

    private CarritoAdapter adapter;
    private List<Zapato> listaArticulos;

    private TextView tvSubtotal, tvEnvio, tvTotalPedido;
    private Button btnFinalizarCompra;
    private final double COSTO_ENVIO = 15.00;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_articulos_anadidos);

        // 1. Conectar Vistas
        RecyclerView recyclerView = findViewById(R.id.recycler_view_carrito);
        tvSubtotal = findViewById(R.id.tv_subtotal);
        tvEnvio = findViewById(R.id.tv_envio);
        tvTotalPedido = findViewById(R.id.tv_total_pedido);
        btnFinalizarCompra = findViewById(R.id.btn_finalizar_compra);

        // 2. RECIBIR la lista de productos del Intent
        Bundle extras = getIntent().getExtras();
        if (extras != null && extras.containsKey("CARRITO_ITEMS")) {
            listaArticulos = (List<Zapato>) extras.getSerializable("CARRITO_ITEMS");
        } else {
            listaArticulos = new ArrayList<>(); // Carrito vacío si no se pasó nada
        }

        // 3. Configurar RecyclerView
        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        adapter = new CarritoAdapter(this, listaArticulos, this);
        recyclerView.setAdapter(adapter);

        // 4. Calcular y mostrar totales iniciales
        calcularYActualizarTotal();

        // 5. Botón de Finalizar Compra: NAVEGACIÓN Y PASE DEL TOTAL
        btnFinalizarCompra.setOnClickListener(v -> {
            if (listaArticulos.isEmpty()) {
                Toast.makeText(this, "El carrito está vacío.", Toast.LENGTH_SHORT).show();
                return;
            }

            double total = calcularTotalNumerico();

            // Navegación a la pantalla de Pago
            Intent intent = new Intent(ArticulosAnadidosActivity.this, RealizarCompraActivity.class);
            intent.putExtra("TOTAL_PEDIDO", total);
            startActivity(intent);
        });
    }

    // --- Lógica del Carrito ---

    @Override
    public void onCantidadCambiada() { calcularYActualizarTotal(); }

    @Override
    public void onItemEliminado(int position) {
        String nombreEliminado = listaArticulos.get(position).getNombre();
        listaArticulos.remove(position);
        adapter.notifyItemRemoved(position);
        calcularYActualizarTotal();
        Toast.makeText(this, nombreEliminado + " eliminado.", Toast.LENGTH_SHORT).show();
    }

    private double calcularTotalNumerico() {
        double subtotal = 0;
        for (Zapato zapato : listaArticulos) {
            subtotal += zapato.getSubtotalItem();
        }
        return subtotal > 0 ? subtotal + COSTO_ENVIO : 0;
    }

    private void calcularYActualizarTotal() {
        double subtotal = 0;
        for (Zapato zapato : listaArticulos) {
            subtotal += zapato.getSubtotalItem();
        }

        double total;
        double costoEnvioDisplay;

        if (subtotal == 0) {
            total = 0;
            costoEnvioDisplay = 0;
        } else {
            total = subtotal + COSTO_ENVIO;
            costoEnvioDisplay = COSTO_ENVIO;
        }

        tvSubtotal.setText(String.format("$%.2f", subtotal));
        tvEnvio.setText(String.format("$%.2f", costoEnvioDisplay));
        tvTotalPedido.setText(String.format("$%.2f", total));
    }
}*/
