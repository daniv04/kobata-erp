import SectionCard from '../SectionCard';
import FormInput from '../FormInput';
import FormSelect from '../FormSelect';
import ClientSearch from './ClientSearch';

export default function ReceptorSection({ data, onChange }) {
  function handleClientSelect(client) {
    onChange('Receptor.Nombre', client.hacienda_name);
    onChange('Receptor.Identificacion.Tipo', client.id_number_type);
    onChange('Receptor.Identificacion.Numero', client.id_number);
    onChange('Receptor.Ubicacion.Provincia', client.province ?? '');
    onChange('Receptor.Ubicacion.Canton', client.canton ?? '');
    onChange('Receptor.Ubicacion.Distrito', client.district ?? '');
    onChange('Receptor.Ubicacion.OtrasSenas', client.address ?? '');
  }
  return (
    <SectionCard
      title="Información del Receptor"
      description="Datos de la empresa que recibe la factura"
    >
      <ClientSearch onSelect={handleClientSelect} />

      <FormInput
        label="Nombre Empresa"
        id="receptor_nombre"
        value={data.Nombre}
        onChange={(value) => onChange('Receptor.Nombre', value)}
        required
      />

      <FormInput
        label="Nombre Comercial"
        id="receptor_nombre_comercial"
        value={data.NombreComercial}
        onChange={(value) => onChange('Receptor.NombreComercial', value)}
      />

      <FormSelect
        label="Tipo de Identificación"
        id="receptor_tipo_id"
        value={data.Identificacion.Tipo}
        onChange={(value) => onChange('Receptor.Identificacion.Tipo', value)}
        options={[
          { value: '01', label: 'Cédula Física' },
          { value: '02', label: 'Cédula Jurídica' },
          { value: '03', label: 'DIMEX' },
          { value: '04', label: 'NITE' },
        ]}
        required
      />

      <FormInput
        label="Número de Identificación"
        id="receptor_numero_id"
        value={data.Identificacion.Numero}
        onChange={(value) => onChange('Receptor.Identificacion.Numero', value)}
        required
      />

      <FormInput
        label="Provincia"
        id="receptor_provincia"
        value={data.Ubicacion.Provincia}
        onChange={(value) => onChange('Receptor.Ubicacion.Provincia', value)}
      />

      <FormInput
        label="Cantón"
        id="receptor_canton"
        value={data.Ubicacion.Canton}
        onChange={(value) => onChange('Receptor.Ubicacion.Canton', value)}
      />

      <FormInput
        label="Distrito"
        id="receptor_distrito"
        value={data.Ubicacion.Distrito}
        onChange={(value) => onChange('Receptor.Ubicacion.Distrito', value)}
      />

      <FormInput
        label="Otras Señas"
        id="receptor_otras_senas"
        value={data.Ubicacion.OtrasSenas}
        onChange={(value) => onChange('Receptor.Ubicacion.OtrasSenas', value)}
      />
    </SectionCard>
  );
}
