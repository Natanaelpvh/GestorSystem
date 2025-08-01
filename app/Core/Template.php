<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Um motor de template simples, inspirado no Blade.
 *
 * Esta classe lida com a compilação de arquivos de view com sintaxe customizada
 * para PHP puro, com suporte a cache, herança de layouts e diretivas.
 * A lógica de compilação garante que views que estendem layouts sejam processadas
 * corretamente, compilando as seções da view filha antes de injetá-las no layout pai.
 */
class Template
{
    /**
     * Caminho para o diretório de views.
     * @var string
     */
    protected string $viewPath;

    /**
     * Caminho para o diretório de cache das views compiladas.
     * @var string
     */
    protected string $cachePath;

    /**
     * Armazena o nome do layout a ser estendido durante a compilação.
     * @var string|null
     */
    protected ?string $layout = null;

    /**
     * Armazena o conteúdo já compilado das seções da view.
     * @var array
     */
    protected array $sections = [];

    /**
     * Array para rastrear views que estão sendo compiladas para evitar recursão infinita.
     * @var array
     */
    private static array $compiling = [];

    /**
     * Construtor da classe Template.
     *
     * Define os caminhos para as views e para o cache, e garante
     * que o diretório de cache exista.
     */
    public function __construct()
    {
        $this->viewPath = dirname(__DIR__, 2) . '/app/Views/';
        $this->cachePath = dirname(__DIR__, 2) . '/storage/cache/views/';

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Renderiza uma view e retorna o HTML.
     *
     * @param string $view O nome da view (ex: 'empresa.index').
     * @param array $data Os dados a serem passados para a view.
     * @return string O HTML renderizado.
     */
    public function render(string $view, array $data = []): string
    {
        $compiledPath = $this->getCompiledPath($view);

        if ($this->isExpired($view, $compiledPath)) {
            $this->compile($view);
        }

        return $this->evaluatePath($compiledPath, $data);
    }

    /**
     * Compila um arquivo de view, gerenciando a herança de layouts e a compilação de diretivas.
     *
     * Este método orquestra todo o processo. Se a view estender um layout, ele primeiro
     * compila o conteúdo de cada seção da view filha. Em seguida, carrega o layout pai
     * e o compila, substituindo as diretivas @yield pelas seções já processadas.
     *
     * @param string $view O nome da view a ser compilada.
     * @throws \LogicException se uma recursão de @include for detectada.
     * @throws \InvalidArgumentException se a view ou o layout não forem encontrados.
     */
    protected function compile(string $view): void
    {
        if (isset(self::$compiling[$view])) {
            throw new \LogicException("Circular @include detected in view [{$view}].");
        }

        self::$compiling[$view] = true;

        $this->layout = null;
        $this->sections = [];

        $viewFile = $this->resolveViewPath($view);
        if (!file_exists($viewFile)) {
            unset(self::$compiling[$view]);
            throw new \InvalidArgumentException("View [{$view}] não encontrada.");
        }
        $content = file_get_contents($viewFile);

        // Verifica se a view estende um layout
        if (preg_match('/@extends\([\'"](.+?)[\'"]\)/', $content, $matches)) {
            $this->layout = $matches[1];
            
            // Extrai e compila o conteúdo de cada seção da view filha
            preg_match_all('/@section\([\'"](.+?)[\'"]\)(.*?)@endsection/s', $content, $sectionMatches, PREG_SET_ORDER);
            
            foreach ($sectionMatches as $match) {
                $sectionName = $match[1];
                $sectionContent = $match[2];
                // Compila o conteúdo da seção ANTES de armazená-lo
                $this->sections[$sectionName] = $this->runAllCompilers($sectionContent);
            }

            // O conteúdo a ser processado agora é o do layout pai
            $layoutFile = $this->resolveViewPath($this->layout);
            if (!file_exists($layoutFile)) {
                unset(self::$compiling[$view]);
                throw new \InvalidArgumentException("Layout [{$this->layout}] não encontrado.");
            }
            $content = file_get_contents($layoutFile);
        }
        
        // Executa todas as compilações no conteúdo final (seja da view ou do layout)
        $finalContent = $this->runAllCompilers($content);

        file_put_contents($this->getCompiledPath($view), $finalContent);

        unset(self::$compiling[$view]);
    }

    /**
     * Roda a cadeia de compiladores em um determinado conteúdo de string.
     *
     * @param string $content O conteúdo a ser compilado.
     * @return string O conteúdo compilado para PHP puro.
     */
    protected function runAllCompilers(string $content): string
    {
        // A ordem aqui é importante para o funcionamento correto.
        $content = $this->compileIncludes($content);
        $content = $this->compileControlStructures($content);
        $content = $this->compileEchos($content);
        $content = $this->compileCsrf($content);
        $content = $this->compileYields($content); // Processa @yield por último.

        return $content;
    }
    
    /**
     * Compila a diretiva {{ ... }} para `<?= htmlspecialchars(...) ?>`.
     *
     * @param string $content
     * @return string
     */
    protected function compileEchos(string $content): string
    {
        return preg_replace('/\{\{\s*(.+?)\s*\}\}/', '<?= htmlspecialchars($1, ENT_QUOTES, \'UTF-8\') ?>', $content);
    }

    /**
     * Compila estruturas de controle como @if, @foreach, etc.
     *
     * @param string $content
     * @return string
     */
    protected function compileControlStructures(string $content): string
    {
        $content = preg_replace('/@if\s*\((.*)\)/', '<?php if($1): ?>', $content);
        $content = preg_replace('/@elseif\s*\((.*)\)/', '<?php elseif($1): ?>', $content);
        $content = preg_replace('/@else/', '<?php else: ?>', $content);
        $content = preg_replace('/@endif/', '<?php endif; ?>', $content);
        $content = preg_replace('/@foreach\s*\((.*)\)/', '<?php foreach($1): ?>', $content);
        $content = preg_replace('/@endforeach/', '<?php endforeach; ?>', $content);
        return $content;
    }

    /**
     * Compila a diretiva @include, garantindo que a sub-view seja compilada.
     *
     * @param string $content
     * @return string
     */
    protected function compileIncludes(string $content): string
    {
        return preg_replace_callback('/@include\([\'"](.+?)[\'"]\)/', function ($matches) {
            $viewToInclude = $matches[1];

            // Garante que a sub-view (include) seja compilada antes de ser incluída.
            $this->ensureCompiled($viewToInclude);

            $compiledPath = $this->getCompiledPath($viewToInclude);
            return "<?php include '{$compiledPath}'; ?>";
        }, $content);
    }
    
    /**
     * Compila a diretiva @yield, substituindo-a pelo conteúdo da seção correspondente.
     *
     * @param string $content O conteúdo do layout.
     * @return string
     */
    protected function compileYields(string $content): string
    {
        return preg_replace_callback('/@yield\([\'"](.+?)[\'"]\)/', function ($matches) {
            $sectionName = $matches[1];
            // Retorna o conteúdo da seção já compilado ou uma string vazia se não existir.
            return $this->sections[$sectionName] ?? '';
        }, $content);
    }

    /**
     * Garante que uma view específica seja compilada e esteja atualizada no cache.
     *
     * Este método é usado para compilar sub-views (incluídas com @include) de forma isolada,
     * para não interferir com o estado da compilação principal (ex: layout e seções).
     *
     * @param string $view O nome da view a ser compilada.
     * @return void
     */
    protected function ensureCompiled(string $view): void
    {
        $compiledPath = $this->getCompiledPath($view);

        if ($this->isExpired($view, $compiledPath)) {
            // Usamos uma nova instância de Template para compilar a sub-view
            // de forma isolada, para não corromper o estado da compilação principal.
            $compiler = new self();
            $compiler->compile($view);
        }
    }

    /**
     * Compila a diretiva @csrf para um campo de input de token CSRF.
     *
     * @param string $content
     * @return string
     */
    protected function compileCsrf(string $content): string
    {
        return preg_replace('/@csrf/', '<input type="hidden" name="_token" value="<?= (new \App\Core\Session())->get(\'_token\'); ?>">', $content);
    }

    /**
     * Retorna o caminho completo para o arquivo de cache compilado.
     *
     * @param string $view
     * @return string
     */
    protected function getCompiledPath(string $view): string
    {
        return $this->cachePath . md5($view) . '.php';
    }

    /**
     * Verifica se o arquivo de view original foi modificado após a criação do cache.
     *
     * @param string $view
     * @param string $compiledPath
     * @return bool
     */
    protected function isExpired(string $view, string $compiledPath): bool
    {
        if (!file_exists($compiledPath)) {
            return true;
        }
        $viewFile = $this->resolveViewPath($view);
        if (!file_exists($viewFile)) {
            return true; // Considera expirado se o arquivo de view original for deletado
        }
        return filemtime($viewFile) > filemtime($compiledPath);
    }

    /**
     * Converte o nome da view no formato 'pasta.arquivo' para um caminho de arquivo real.
     *
     * @param string $view
     * @return string
     */
    protected function resolveViewPath(string $view): string
    {
        return $this->viewPath . str_replace('.', '/', $view) . '.php';
    }

    /**
     * Executa o arquivo PHP compilado em um buffer e retorna seu output.
     *
     * @param string $path O caminho para o arquivo compilado.
     * @param array $data Os dados a serem extraídos como variáveis para a view.
     * @return string O conteúdo renderizado.
     */
    protected function evaluatePath(string $path, array $data): string
    {
        ob_start();
        extract($data, EXTR_SKIP);
        require $path;
        return ob_get_clean();
    }
}