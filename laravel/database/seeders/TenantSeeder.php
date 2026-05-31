<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Tenancy\Services\TenantProvisioningService;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function __construct(
        private readonly TenantProvisioningService $provisioning,
    ) {
    }

    public function run(): void
    {
        $tenants = [
            ['name' => 'Escola Estadual São Paulo', 'slug' => 'escola-sp', 'domain' => 'escola-sp.geffin.localhost'],
            ['name' => 'Colégio Municipal Rio de Janeiro', 'slug' => 'colegio-rj', 'domain' => 'colegio-rj.geffin.localhost'],
            ['name' => 'Instituto Federal Minas Gerais', 'slug' => 'ifmg', 'domain' => 'ifmg.geffin.localhost'],
            ['name' => 'Centro Educacional Bahia', 'slug' => 'ce-bahia', 'domain' => 'ce-bahia.geffin.localhost'],
            ['name' => 'Escola Técnica Paraná', 'slug' => 'et-parana', 'domain' => 'et-parana.geffin.localhost'],
            ['name' => 'Colégio Estadual Goiás', 'slug' => 'ceg-goias', 'domain' => 'ceg-goias.geffin.localhost'],
            ['name' => 'Escola Municipal Pernambuco', 'slug' => 'em-pernambuco', 'domain' => 'em-pernambuco.geffin.localhost'],
            ['name' => 'Instituto Educacional Ceará', 'slug' => 'ie-ceara', 'domain' => 'ie-ceara.geffin.localhost'],
            ['name' => 'Colégio Santa Maria', 'slug' => 'santa-maria', 'domain' => 'santa-maria.geffin.localhost'],
            ['name' => 'Escola Dom Bosco', 'slug' => 'dom-bosco', 'domain' => 'dom-bosco.geffin.localhost'],
            ['name' => 'Centro de Ensino Sagrado Coração', 'slug' => 'sagrado-coracao', 'domain' => 'sagrado-coracao.geffin.localhost'],
            ['name' => 'Escola Nossa Senhora de Fátima', 'slug' => 'ns-fatima', 'domain' => 'ns-fatima.geffin.localhost'],
            ['name' => 'Colégio São José', 'slug' => 'sao-jose', 'domain' => 'sao-jose.geffin.localhost'],
            ['name' => 'Escola Estadual Rio Grande do Sul', 'slug' => 'ee-rgs', 'domain' => 'ee-rgs.geffin.localhost'],
            ['name' => 'Instituto Educacional Santa Catarina', 'slug' => 'ie-sc', 'domain' => 'ie-sc.geffin.localhost'],
            ['name' => 'Colégio Batista Aliança', 'slug' => 'batista-alianca', 'domain' => 'batista-alianca.geffin.localhost'],
            ['name' => 'Escola Municipal Monteiro Lobato', 'slug' => 'monteiro-lobato', 'domain' => 'monteiro-lobato.geffin.localhost'],
            ['name' => 'Instituto Tecnológico do Nordeste', 'slug' => 'itn', 'domain' => 'itn.geffin.localhost'],
            ['name' => 'Centro Educacional Alpha', 'slug' => 'ce-alpha', 'domain' => 'ce-alpha.geffin.localhost'],
            ['name' => 'Colégio Cristão Vida Nova', 'slug' => 'vida-nova', 'domain' => 'vida-nova.geffin.localhost'],
            ['name' => 'Escola Técnica Amazonas', 'slug' => 'et-amazonas', 'domain' => 'et-amazonas.geffin.localhost'],
            ['name' => 'Instituto Educacional Vitória', 'slug' => 'ie-vitoria', 'domain' => 'ie-vitoria.geffin.localhost'],
            ['name' => 'Colégio Estadual Maranhão', 'slug' => 'ce-maranhao', 'domain' => 'ce-maranhao.geffin.localhost'],
            ['name' => 'Escola Adventista Esperança', 'slug' => 'adventista-esperanca', 'domain' => 'adventista-esperanca.geffin.localhost'],
            ['name' => 'Centro de Ensino Integral Paraíba', 'slug' => 'cei-paraiba', 'domain' => 'cei-paraiba.geffin.localhost'],
            ['name' => 'Instituto Federal do Tocantins', 'slug' => 'ift', 'domain' => 'ift.geffin.localhost'],
            ['name' => 'Escola Municipal Rui Barbosa', 'slug' => 'rui-barbosa', 'domain' => 'rui-barbosa.geffin.localhost'],
            ['name' => 'Colégio Nova Geração', 'slug' => 'nova-geracao', 'domain' => 'nova-geracao.geffin.localhost'],
            ['name' => 'Centro Educacional Piauí', 'slug' => 'ce-piaui', 'domain' => 'ce-piaui.geffin.localhost'],
            ['name' => 'Escola Estadual Tiradentes', 'slug' => 'tiradentes', 'domain' => 'tiradentes.geffin.localhost'],
        ];

        foreach ($tenants as $data) {
            $this->provisioning->provision(
                name: $data['name'],
                slug: $data['slug'],
                domain: $data['domain'],
            );
        }
    }
}
