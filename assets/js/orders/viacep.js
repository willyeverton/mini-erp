/**
 * Integração com a API ViaCEP
 * Consulta endereço a partir do CEP e preenche campos automaticamente
 */
$(document).ready(function () {
  // Elementos do formulário
  const cepInput = $('#zipcode');
  if (!cepInput.length) return;

  const addressInput = document.getElementById('address');
  const districtInput = document.getElementById('district');
  const cityInput = document.getElementById('city');
  const stateInput = document.getElementById('state');

  // Máscara para o campo de CEP (opcional)
  cepInput.on('input', function (e) {
    let value = $(this).val().replace(/\D/g, '');
    if (value.length > 5) {
      value = value.substring(0, 5) + '-' + value.substring(5, 8);
    }
    $(this).val(value);
  });

  // Consulta CEP quando o campo perde o foco
  cepInput.on('blur', function () {
    const cep = $(this).val().replace(/\D/g, '');

    if (cep.length !== 8) {
      return;
    }

    // Mostrar indicador de carregamento
    const originalState = setFieldsState(true);

    // Realizar consulta ao ViaCEP
    $.ajax({
      url: `https://viacep.com.br/ws/${cep}/json/`,
      dataType: 'json',
      success: function (data) {
        if (data.erro) {
          showError('CEP não encontrado');
          return;
        }
        console.log(data);

        // Preencher campos com os dados retornados
        if (addressInput) addressInput.value = data.logradouro;
        if (districtInput) districtInput.value = data.bairro;
        if (cityInput) cityInput.value = data.localidade;
        if (stateInput) stateInput.value = data.uf;

        // Focar no campo de número
        const numberInput = $('#number');
        if (numberInput.length) numberInput.focus();
      },
      error: function () {
        showError('Erro ao consultar o CEP. Verifique sua conexão.');
      },
      complete: function () {
        // Restaurar estado original dos campos
        setFieldsState(false, originalState);
      }
    });
  });

  /**
   * Habilita/desabilita campos durante a consulta
   * @param {boolean} loading - Se está carregando
   * @param {Object} originalState - Estado original dos campos
   * @returns {Object} - Estado original dos campos
   */
  function setFieldsState(loading, originalState = {}) {
    const fields = [addressInput, districtInput, cityInput, stateInput];

    if (loading) {
      // Salvar estado original e desabilitar campos
      const state = {};
      fields.forEach(field => {
        if (!field) return;
        state[field.id] = {
          disabled: field.disabled,
          value: field.value
        };
        field.disabled = true;
        field.classList.add('loading');
      });
      return state;
    } else {
      // Restaurar estado original
      fields.forEach(field => {
        if (!field || !originalState[field.id]) return;
        field.disabled = originalState[field.id].disabled;
        field.classList.remove('loading');
      });
    }
  }

  /**
   * Exibe mensagem de erro
   * @param {string} message - Mensagem de erro
   */
  function showError(message) {
    // Restaurar campos para valores vazios
    if (addressInput) addressInput.value = '';
    if (districtInput) districtInput.value = '';
    if (cityInput) cityInput.value = '';
    if (stateInput) stateInput.value = '';

    // Exibir erro (pode ser personalizado conforme sua UI)
    $('#checkout-form').showFormAlert(message, 'danger');
  }
});
