# BRANCH PREDICTOR SIMULATOR

O Branch Predictor Simulator é uma ferramenta web para simulação dos preditores local (BHT) e global (GHT). O intuito da página é auxiliar no aprendizado acadêmico, para que estudantes possam executar e entender o funcionamento de tais tecnologias.

Este projeto é resultado do trabalho final da disciplina Arquitetura de Computadores II do curso de Ciência da Computação, ministrada pelo Professor Doutor Luiz Henrique Andrade Correia, do Departamento de Ciência da Computação, UFLA.

# TECNOLOGIAS UTILIZADAS

As linguagens utilizadas foram PHP7.3.6 e Javascript na biblioteca JQuery 3.4.1.

# INSTALAÇÃO LOCAL

Para executar localmente é necessária a instalação do servidor XAMPP, que inclui o Apache 2.4 e o PHP.

Para executar o projeto, além de inicializar o Apache, seguir os passos no diretório onde foi instalado o Xampp:
- cd xampp
- cd htdocs
- git clone https://github.com/phumacinha/Branch-Predictor-Simulator.git
- No navegador, pesquisar *localhost/Branch-Predictor-Simulator*

# LINK HOSPEDADO

O projeto está hospedado e pode ser consultado neste [link](http://branchprediction.epizy.com/).

# MANUAL DO USUÁRIO

A descrição de uso pode ser encontrada no [site](http://branchprediction.epizy.com/) na sessão “Help”.

# DESCRIÇÃO E FUNCIONAMENTO

Os preditores (m, n) são tecnologias desenvolvidas para auxiliar o hardware em situações de desvios de código (estruturas condicionais e de repetição, chamadas de funções, recursões, etc.), de modo que tentam “adivinhar” o comportamento do desvio com base nos acontecimentos anteriores.

A abordagem desse projeto são preditores de 1 e 2 bits, ou seja, a predição atual depende, respectivamente, dos 1 ou 2 últimos acontecimentos, como mostrado nas figuras a seguir.

<p align="center">
  <img src="/readme-images/state-machine-1-bit.png">
</p>

<p align="center">
  <img src="/readme-images/state-machine-2-bit.png">
</p>

## Parâmetros de funcionamento
- m: número de bits do endereço usados para indexação da predição log<sub>2</sub><# of indexes> = m;
- n: número de bits que guardam o comportamento da predição global;
- history_size: número de bits que guardam o comportamento da predição no índice (define a márquina de estados - contadores);
- counter: contadores individuais para cada índice com history_size bits (se history_size==1, então max(counter)==1; se history_size==2, então max(counter)==3), e seu valor determina a predição:
  - 1 bit
    - 0 = N (não tomado)
    - 1 = T (tomado)
  - 2 bits
    - 0 = N
    - 1 = N
    - 2 = T
    - 3 = T

O contador do índice da predição é incrementado caso o resultado real do desvio seja T e decrementado caso seja N. Eles, ainda, saturam nos extremos e, caso ocorra uma tentiva de extrapolação, o valor é mantido.

## BHT - Preditor Local de Desvios

O BHT (Branch History Table) avalia apenas o comportamento local dos índices para realizar a predição, ou seja, é um preditor(m, 0) – n é sempre igual a 0.

Para realizar a indexação, primeiramente são removidos os dois bits menos significativos do endereço (bits de verificação), e faz-se o cálculo de m especificado acima, nos parâmetros de funcionamento. Os m LSBits do endereço são usados para indexar a predição.

Com o índice calculado, verifica-se a predição determinada pelo valor do contador e, após a comparação com o desvio real, as variáveis de precisão, histórico e contador são atualizadas.

## GHT – Preditor Global de Desvios

O GHT (Global History Table) é um preditos(m, n) que concatena n e m para criar n + m índices na tabela.

Para indexação da predição, as operações de remoção dos bits de verificação e cálculo de m são feitos como no BHT. Depois disso, os n bits de comportamento do GHT são concatenados, de forma que os n bits são usados como MSB e os m bits como LSB. O resultado da concatenação é o índice da tabela onde será feita a predição.

Por decisões de projeto,...

Depois de calculado o índice, além das operações também feitas no BHT, o GHT deve atualizar o histórico global. Isso é feito "empurrando" o bit menos significativo para a esquerda, sendo adicionado 1 caso o desvio tenha sido tomado e 0 caso contrário. A string onde o histórico é guardado tem tamanho n e toda vez que ela é atualizada o antigo MSB é descartado.

# EXPERIMENTOS

