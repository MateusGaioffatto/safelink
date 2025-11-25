// Dados do quiz - 11 perguntas
const quizQuestions = [
  {
    question: "O que você deve fazer ao receber um email pedindo sua senha do banco?",
    options: [
      "Responder com a senha",
      "Clicar no link do email",
      "Ignorar e deletar o email",
      "Encaminhar para amigos"
    ],
    correct: 2
  },
  {
    question: "Qual destes é um exemplo de senha mais segura?",
    options: [
      "123456",
      "senha123",
      "Maria2024!",
      "01011990"
    ],
    correct: 2
  },
  {
    question: "O que o HTTPS indica em um site?",
    options: [
      "Que o site é mais rápido",
      "Que a conexão é criptografada",
      "Que o site é gratuito",
      "Que o site tem mais recursos"
    ],
    correct: 1
  },
  {
    question: "O que é phishing?",
    options: [
      "Um tipo de pescaria online",
      "Uma técnica de golpe para obter dados pessoais",
      "Um método de compra online",
      "Um tipo de vírus de computador"
    ],
    correct: 1
  },
  {
    question: "Por que é importante manter software atualizado?",
    options: [
      "Para ter os recursos mais recentes",
      "Para corrigir vulnerabilidades de segurança",
      "Para melhorar a velocidade do computador",
      "Para liberar mais espaço em disco"
    ],
    correct: 1
  },
];

let currentQuestion = 0;
let userAnswers = new Array(quizQuestions.length).fill(null);
let score = 0;
let quizCompleted = false;

function flipCard(card) {
  card.classList.toggle('flipped');
}

function loadQuestion() {
  const question = quizQuestions[currentQuestion];
  document.getElementById('quizQuestion').textContent = question.question;
  
  const optionsContainer = document.getElementById('quizOptions');
  optionsContainer.innerHTML = '';
  
  // LIMPAR o resultado anterior - APENAS resetar a classe
  const result = document.getElementById('quizResult');
  result.className = 'quiz-result';
  result.innerHTML = '';
  // REMOVI: result.style.display = 'none'; - Deixa o CSS controlar a visibilidade
  
  question.options.forEach((option, index) => {
    const optionElement = document.createElement('div');
    optionElement.className = 'quiz-option';
    optionElement.textContent = option;
    optionElement.onclick = function() {
      checkAnswer(this, index);
    };
    optionsContainer.appendChild(optionElement);
  });
  
  document.getElementById('quizProgress').textContent = `Pergunta ${currentQuestion + 1} de ${quizQuestions.length}`;
  document.getElementById('prevBtn').disabled = currentQuestion === 0;
  
  // Mostrar "Finalizar" na última pergunta
  if (currentQuestion === quizQuestions.length - 1) {
    document.getElementById('nextBtn').textContent = 'Finalizar';
    document.getElementById('nextBtn').disabled = false;
  } else {
    document.getElementById('nextBtn').textContent = 'Próxima';
    document.getElementById('nextBtn').disabled = false;
  }
  
  // Restaurar resposta anterior se existir
  if (userAnswers[currentQuestion] !== null) {
    const options = optionsContainer.querySelectorAll('.quiz-option');
    const selectedIndex = userAnswers[currentQuestion];
    const correctIndex = quizQuestions[currentQuestion].correct;
    
    options.forEach((opt, index) => {
      opt.style.pointerEvents = 'none'; // Bloquear clique novamente
      if (index === correctIndex) {
        opt.style.background = '#28a745';
        opt.style.color = 'white';
        opt.style.borderColor = '#28a745';
      } else if (index === selectedIndex && selectedIndex !== correctIndex) {
        opt.style.background = '#dc3545';
        opt.style.color = 'white';
        opt.style.borderColor = '#dc3545';
      }
    });
    
    // Mostrar resultado salvo
    if (selectedIndex === correctIndex) {
      result.className = 'quiz-result result-correct show';
      result.innerHTML = '<i class="fas fa-check-circle"></i> ' + getFeedbackMessage(currentQuestion, true);
    } else {
      result.className = 'quiz-result result-incorrect show';
      result.innerHTML = '<i class="fas fa-times-circle"></i> ' + getFeedbackMessage(currentQuestion, false);
    }
  }
}

function checkAnswer(option, selectedIndex) {
  const result = document.getElementById('quizResult');
  const options = option.parentElement.querySelectorAll('.quiz-option');
  const question = quizQuestions[currentQuestion];
  const isCorrect = selectedIndex === question.correct;
  
  options.forEach(opt => {
    opt.style.pointerEvents = 'none';
  });
  
  options.forEach((opt, index) => {
    // Resetar estilos primeiro
    opt.style.background = '';
    opt.style.color = '';
    opt.style.borderColor = '';
    
    if (index === question.correct) {
      opt.style.background = '#28a745';
      opt.style.color = 'white';
      opt.style.borderColor = '#28a745';
    } else if (index === selectedIndex && !isCorrect) {
      opt.style.background = '#dc3545';
      opt.style.color = 'white';
      opt.style.borderColor = '#dc3545';
    }
  });
  
  // Salvar resposta do usuário
  userAnswers[currentQuestion] = selectedIndex;
  
  // MOSTRAR resultado
  if (isCorrect) {
    result.className = 'quiz-result result-correct show';
    result.innerHTML = '<i class="fas fa-check-circle"></i> ' + getFeedbackMessage(currentQuestion, true);
  } else {
    result.className = 'quiz-result result-incorrect show';
    result.innerHTML = '<i class="fas fa-times-circle"></i> ' + getFeedbackMessage(currentQuestion, false);
  }
}

function getFeedbackMessage(questionIndex, isCorrect) {
  const messages = {
    0: {
      correct: "Excelente! Bancos legítimos nunca solicitam senhas por email.",
      incorrect: "Cuidado! Bancos nunca pedem senhas por email. Sempre ignore e delete essas mensagens."
    },
    1: {
      correct: "Perfeito! Senhas fortes combinam letras, números, símbolos e têm boa complexidade.",
      incorrect: "Esta senha é muito fraca. Senhas fortes precisam de letras maiúsculas, minúsculas, números e símbolos."
    },
    2: {
      correct: "Correto! HTTPS garante que sua conexão com o site é criptografada e segura.",
      incorrect: "O HTTPS não está relacionado à velocidade, mas sim à segurança da conexão."
    },
    3: {
      correct: "Isso mesmo! Phishing é uma técnica de engenharia social para obter dados pessoais.",
      incorrect: "Phishing não é um tipo de pescaria, mas sim uma técnica fraudulenta para roubar dados."
    },
    4: {
      correct: "Exatamente! Atualizações frequentes corrigem vulnerabilidades críticas de segurança.",
      incorrect: "A principal razão para atualizar software é corrigir falhas de segurança, não apenas ter novos recursos."
    }
  };
  
  return messages[questionIndex] ? messages[questionIndex][isCorrect ? 'correct' : 'incorrect'] : 
         (isCorrect ? "Resposta correta!" : "Resposta incorreta.");
}

function nextQuestion() {
  if (currentQuestion < quizQuestions.length - 1) {
    currentQuestion++;
    loadQuestion();
  } else {
    showResults();
  }
}

function prevQuestion() {
  if (currentQuestion > 0) {
    currentQuestion--;
    loadQuestion();
  }
}

function showResults() {
  // Calcular pontuação
  score = 0;
  userAnswers.forEach((answer, index) => {
    if (answer === quizQuestions[index].correct) {
      score++;
    }
  });
  
  const percentage = Math.round((score / quizQuestions.length) * 100);
  
  // Mensagem baseada na pontuação
  let message = '';
  if (percentage >= 80) {
    message = '🎉 Excelente! Você é um expert em segurança online!';
  } else if (percentage >= 60) {
    message = '👍 Bom trabalho! Você tem bons conhecimentos de segurança.';
  } else if (percentage >= 40) {
    message = '💡 Não foi mal! Revise as dicas para melhorar sua segurança.';
  } else {
    message = '📚 Hora de estudar! Sua segurança online precisa de atenção.';
  }
  
  const resultsHTML = `
    <div class="quiz-results">
      <h3><i class="fas fa-trophy"></i> Quiz Concluído!</h3>
      <div class="score-circle">
        <div class="score-value">${percentage}%</div>
        <div class="score-text">${score}/${quizQuestions.length} corretas</div>
      </div>
      <p class="score-message">${message}</p>
      <button class="restart-btn" onclick="restartQuiz()">
        <i class="fas fa-redo"></i> Fazer Quiz Novamente
      </button>
    </div>
  `;
  
  document.getElementById('quizContainer').innerHTML = resultsHTML;
  quizCompleted = true;
}

function restartQuiz() {
  currentQuestion = 0;
  userAnswers = new Array(quizQuestions.length).fill(null);
  score = 0;
  quizCompleted = false;
  
  // Recarregar o container do quiz
  document.getElementById('quizContainer').innerHTML = `
    <h3 class="quiz-title">Teste seus conhecimentos sobre segurança online!</h3>
    <div class="quiz-question" id="quizQuestion"></div>
    <div class="quiz-options" id="quizOptions"></div>
    <div class="quiz-result" id="quizResult"></div>
    
    <div class="quiz-progress" id="quizProgress">Pergunta 1 de ${quizQuestions.length}</div>
    <div class="quiz-navigation">
      <button class="quiz-btn" id="prevBtn" onclick="prevQuestion()" disabled>Anterior</button>
      <button class="quiz-btn" id="nextBtn" onclick="nextQuestion()">Próxima</button>
    </div>
  `;
  
  loadQuestion();
}

// Inicializar o quiz
loadQuestion();